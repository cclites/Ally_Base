<?php
namespace App\Billing;

use App\AuditableModel;
use App\Client;
use App\Billing\Contracts\ChargeableInterface;
use Carbon\Carbon;

/**
 * App\Billing\ClientPayer
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \App\Billing\Payer $payer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class ClientPayer extends AuditableModel
{
    protected $orderedColumn = 'priority';
    protected $guarded = ['id'];
    protected $with = ['payer'];
    protected $appends = ['payer_name'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'priority' => 'integer',
    ];

    ///////////////////////////////////////
    /// Payment Allocation Types
    ///////////////////////////////////////

    const ALLOCATION_BALANCE = 'balance';
    const ALLOCATION_WEEKLY = 'weekly';
    const ALLOCATION_MONTHLY = 'monthly';
    const ALLOCATION_DAILY = 'daily';
    const ALLOCATION_SPLIT = 'split';

    /**
     * @var string[]
     */
    public static $allocationTypes = [
        self::ALLOCATION_BALANCE,
        self::ALLOCATION_WEEKLY,
        self::ALLOCATION_MONTHLY,
        self::ALLOCATION_DAILY,
        self::ALLOCATION_SPLIT,
    ];

    ///////////////////////////////////////
    /// Boot method
    ///////////////////////////////////////

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obj) {
            $obj->priority = self::getNextPriority($obj->client_id);
        });
    }

    public static $allowanceTypes = [
        'daily', 'weekly', 'monthly'
    ];

    /**
     * @var array
     */
    protected $newInvoiceAmounts = [];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    ////////////////////////////////////
    //// Mutators
    ////////////////////////////////////

    /**
     * Get the name of the Payer.
     *
     * @return string
     */
    public function getPayerNameAttribute() : string
    {
        return $this->payer_id == null ? '(Client)' : $this->payer->name;
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Get the payment method for this payer
     *
     * @return \App\Billing\Contracts\ChargeableInterface
     */
    function getPaymentMethod(): ChargeableInterface
    {
        if ($this->payer_id === null) {
            // Private pay
            return $this->client->getPaymentMethod();
        }

        // Fall back to provider pay for all other payments.
        return $this->client->business;
    }

    /**
     * Returns the next free number for priority in the order sequence.
     *
     * @param int $client
     * @return int
     */
    public static function getNextPriority(int $client) : int
    {
        return self::select(\DB::raw('coalesce(max(`priority`), 0) as max_priority'))
            ->where('client_id', $client)
            ->get()
            ->first()
            ->max_priority + 1;
    }

    /**
     * Increases the priority value for all of the users contacts
     * at the given index and above, while skipping the excluded contact ID.
     * Used to shift priority down in order to raise the priority for a specific contact.
     *
     * @param int $client_id
     * @param int $priority
     * @param int $exclude_id
     * @return void
     */
    public static function shiftPriorityDownAt(int $client_id, int $priority, int $exclude_id) : void
    {
        $index = $priority;

        self::where('client_id', $client_id)
            ->where('priority', '>=', $priority)
            ->where('id', '!=', $exclude_id)
            ->orderBy('priority')
            ->get()
            ->each(function ($item, $key) use (&$index) {
                $index = $index + 1;
                $item->update(['priority' => $index]);
            });
    }

    /**
     * Decreases the priority value for all of the users contacts
     * above the given index.  Used to bump the priority up when a 
     * contact is deleted.
     *
     * @param int $client_id
     * @param int $priority
     * @return void
     */
    public static function shiftPriorityUpAt($client_id, $priority)
    {
        $index = $priority;

        self::where('client_id', $client_id)
            ->where('priority', '>', $priority)
            ->orderBy('priority')
            ->get()
            ->each(function ($item, $key) use (&$index) {
                $item->update(['priority' => $index]);
                $index = $index + 1;
            });
    }

    /**
     * Get the starting day of the week for this payer (0 = Sunday, 6 = Saturday)
     *
     * @return int
     */
    function getStartOfWeek(): int
    {
        return $this->payer->week_start;
    }

    /**
     * Get the current payment allowance for a specific date
     *
     * @param string $date
     * @return float
     */
    function getAllowance(string $date): float
    {
        $date = Carbon::parse($date);

        switch($this->payment_allocation) {
            case 'daily':
                $firstDay = $date->toDateString();
                $lastDay = $date->toDateString();
                break;
            case 'weekly':
                $firstDay = $date->copy()->startOfWeek()->addDays($this->getStartOfWeek() - 1)->toDateString();
                $lastDay = $date->copy()->endOfWeek()->addDays($this->getStartOfWeek() - 1)->toDateString();
                break;
            case 'monthly':
                $firstDay = $date->copy()->startOfMonth()->toDateString();
                $lastDay = $date->copy()->endOfMonth()->toDateString();
                break;
            default:
                return 9999999.99;  // No allowance (just a high float that is inconceivable for a single invoice)
        }

        // Calculate data from existing invoice items in database
        $currentSum = InvoiceItem::whereHas('invoice', function ($invoice) {
            $invoice->where('client_id', $this->client_id)->where('payer_id', $this->payer_id);
        })
            ->whereBetween('date', [$firstDay . ' 00:00:00', $lastDay . ' 23:59:59'])
            ->sum('amount_due') ?? 0;

        // Calculate data from newly added data (addAmountInvoiced)
        $currentDay = $firstDay;
        do {
            $currentSum = bcadd($currentSum, $this->newInvoiceAmounts[$currentDay] ?? 0, 2);
            $currentDay = Carbon::parse($currentDay)->addDay()->toDateString();
        }
        while ($currentDay < $lastDay);

        return (float) $currentSum;
    }

    /**
     * Add a new amount that has been invoiced, this is used to track data not yet persisted in invoice items
     *
     * @param float $amount
     * @param string $date
     */
    function addAmountInvoiced(float $amount, string $date): void
    {
        $this->newInvoiceAmounts[$date] = bcadd($this->newInvoiceAmounts[$date] ?? 0, $amount, 2);
    }
}
