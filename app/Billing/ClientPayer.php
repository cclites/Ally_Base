<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Exceptions\PaymentMethodError;
use App\Business;
use App\Client;
use App\Billing\Contracts\ChargeableInterface;
use App\Contracts\HasAllyFeeInterface;
use App\Data\DateRange;
use App\Traits\HasAddressesAndNumbers;
use App\Traits\HasAllyFeeTrait;
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
 * @property float|null $payment_allowance
 * @property float|null $split_percentage
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read string $payer_name
 * @property-read \App\Billing\Payer $payer
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $paymentMethod
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientPayer query()
 * @mixin \Eloquent
 */
class ClientPayer extends AuditableModel implements HasAllyFeeInterface
{
    use HasAllyFeeTrait;

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
    const ALLOCATION_MANUAL = 'manual';

    /**
     * @var string[]
     */
    public static $allocationTypes = [
        self::ALLOCATION_BALANCE,
        self::ALLOCATION_WEEKLY,
        self::ALLOCATION_MONTHLY,
        self::ALLOCATION_DAILY,
        self::ALLOCATION_SPLIT,
        self::ALLOCATION_MANUAL,
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

    public function paymentMethod()
    {
        return $this->morphTo('payment_method');
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

    function name(): string
    {
        return $this->isPrivatePay() ? $this->client->name() : $this->getPayer()->name();
    }

    /**
     * @return \App\Billing\Payer
     */
    function getPayer(): Payer
    {
        return $this->payer;
    }

    function isPrivatePay(): bool
    {
        return $this->getPayer()->isPrivatePay();
    }

    function isOffline(): bool
    {
        return $this->getPayer()->isOffline();
    }

    function getUniqueKey(): string
    {
        if ($this->isPrivatePay()) {
            return (string) $this->payer_id . ':' . $this->getPrivatePayer()->id;
        }

        return (string) $this->id;
    }

    function getPaymentMethod(): ChargeableInterface
    {
        if ($this->getPayer()->isPrivatePay()) {
            if (!$paymentMethod = $this->client->getPaymentMethod()) {
                throw new PaymentMethodError("No payment method is assigned to the private payer.");
            }
            return $paymentMethod;
        }

        if (($method = $this->paymentMethod) && $method instanceof ChargeableInterface) {
            return $method;
        }

        if ($method = $this->getPayer()->getPaymentMethod()) {
            if ($method instanceof Business) {
                $method = $this->client->business;
            }
            return $method;
        }

        throw new PaymentMethodError("No payment method is available for the payer.");
    }


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
     * @return bool
     */
    function isManualType(): bool
    {
        return $this->payment_allocation === self::ALLOCATION_MANUAL;
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
        return (float) ($this->isSplitType() ? $this->split_percentage : 1.0);
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
            case self::ALLOCATION_DAILY:
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();
                break;
            case self::ALLOCATION_WEEKLY:
                list($start, $end) = alterStartOfWeekDay((int) $this->payer->week_start, function() use ($date) {
                    return [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()];
                });
                break;
            case self::ALLOCATION_MONTHLY:
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
            $invoice->where('client_id', $this->client_id)->where('client_payer_id', $this->id);
        })
            ->whereBetween('date', [$dateRange->start()->toDateTimeString(), $dateRange->end()->toDateTimeString()])
            ->sum('amount_due') ?? 0;

        $allowance = bcsub($this->payment_allowance, $currentSum, 4);
        return round($allowance, 2);
    }

    /**
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage()
    {
        try {
            return $this->getPaymentMethod()->getAllyPercentage();
        }
        catch (\Exception $e) {}

        return (float) config('ally.credit_card_fee');
    }
}
