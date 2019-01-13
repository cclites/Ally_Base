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
    protected $guarded = ['id', 'payer_name', 'payer'];
    protected $with = ['payer'];
    protected $appends = ['payer_name'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'client_id' => 'integer',
        'payer_id' => 'integer',
        'priority' => 'integer',
        'payment_allowance' => 'float',
        'split_percentage' => 'float',
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
     * Remove missing ClientPayers and update existing with the given
     * request values.
     *
     * @param \App\Client $client
     * @param array|null $payers
     * @return bool
     */
    public static function sync(Client $client, ?iterable $payers) : bool
    {
        try {
            $new = collect($payers)->filter(function($item) {
                return ! isset($item['id']);
            });

            $existing = collect($payers)->filter(function($item) {
                return isset($item['id']);
            });

            $ids = $existing->pluck('id');
            if (count($ids)) {
                // remove all items with ids that aren't in the current array
                ClientPayer::where('client_id', $client->id)
                    ->whereNotIn('id', $ids)
                    ->delete();

                // update the existing items in case they changed
                foreach($existing as $item) {
                    if ($payer = ClientPayer::where('id', $item['id'])->first()) {
                        $payer->update($item);
                    }
                }
            } else {
                // clear
                ClientPayer::where('client_id', $client->id)->delete();
            }

            // create new issues from the issues that have no id
            foreach($new as $item) {
                ClientPayer::create(array_merge($item, ['client_id' => $client->id]));
            }
            
            return true;
        } catch (\Exception $ex) {
            \Log::debug($ex->getMessage());
            return false;
        }
    }
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
