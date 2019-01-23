<?php
namespace App\Billing;

use App\AuditableModel;
use App\Client;
use App\Billing\Contracts\ChargeableInterface;
use App\Data\DateRange;
use Carbon\Carbon;

/**
 * \App\Billing\ClientPayer
 *
 * @property int $id
 * @property int $client_id
 * @property int $payer_id
 * @property string|null $policy_number
 * @property string $effective_start
 * @property string $effective_end
 * @property string|null $payment_allocation
 * @property float $payment_allowance
 * @property float $split_percentage
 * @property int $priority
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read string $payer_name
 * @property-read \App\Billing\Payer $payer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer whereEffectiveEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer whereEffectiveStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer wherePaymentAllocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer wherePaymentAllowance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer wherePolicyNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer whereSplitPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer whereUpdatedAt($value)
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

    /**
     * @var array
     */
    protected $newInvoiceAmounts = [];

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


    public static $allowanceTypes = [
        self::ALLOCATION_WEEKLY,
        self::ALLOCATION_MONTHLY,
        self::ALLOCATION_DAILY,
    ];


    ///////////////////////////////////////
    /// Static methods
    ///////////////////////////////////////

    protected static function boot()
    {
        parent::boot();
    }

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
     * @return bool
     */
    function isAllowanceType(): bool
    {
        return in_array($this->payment_allocation, self::$allowanceTypes);
    }

    /**
     * @return bool
     */
    function isBalanceType(): bool
    {
        return $this->payment_allocation === self::ALLOCATION_BALANCE;
    }

    /**
     * @return bool
     */
    function isSplitType(): bool
    {
        return $this->payment_allocation === self::ALLOCATION_SPLIT;
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
     * @return float
     */
    function getSplitPercentage(): float
    {
        return $this->isSplitType() ? $this->split_percentage : 1.0;
    }

    /**
     * Return the applicable payment allowance date range for a specific date of service
     *
     * @param string $date
     * @return \App\Data\DateRange|null
     */
    function getAllowanceRange(string $date): ?DateRange
    {
        $date = Carbon::parse($date);

        switch($this->payment_allocation) {
            case 'daily':
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();
                break;
            case 'weekly':
                $start = $date->copy()->startOfWeek()->addDays($this->getStartOfWeek() - 1);
                $end = $date->copy()->endOfWeek()->addDays($this->getStartOfWeek() - 1);
                break;
            case 'monthly':
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
                break;
            default:
                return null;
        }

        return new DateRange($start, $end);
    }

    /**
     * Get the current payment allowance for a specific date
     *
     * @param string $date
     * @return float
     */
    function getAllowance(string $date): float
    {
        $dateRange = $this->getAllowanceRange($date);
        if ($dateRange === null) {
            return 9999999.99;  // No allowance (just a high float that is inconceivable for a single invoice)
        }

        // Calculate data from existing invoice items in database
        $currentSum = ClientInvoiceItem::whereHas('invoice', function ($invoice) {
            $invoice->where('client_id', $this->client_id)->where('payer_id', $this->payer_id);
        })
            ->whereBetween('date', [$dateRange->start->toDateTimeString(), $dateRange->end->toDateTimeString()])
            ->sum('amount_due') ?? 0;

        $allowance = bcsub($this->payment_allowance, $currentSum, 4);
        return round($allowance, 2);
    }
}
