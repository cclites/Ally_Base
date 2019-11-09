<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Exceptions\PaymentMethodError;
use App\Business;
use App\Businesses\Timezone;
use App\Client;
use App\Contracts\BelongsToBusinessesInterface;
use App\Events\PaymentFailed;
use App\Shift;
use App\Traits\BelongsToOneBusiness;
use App\Traits\ScrubsForSeeding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Billing\Payment
 *
 * @property int $id
 * @property int|null $client_id
 * @property int $business_id
 * @property string|null $payment_type
 * @property float|null $amount
 * @property string|null $transaction_id
 * @property string|null $transaction_code
 * @property int $adjustment
 * @property string|null $notes
 * @property int|null $success
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float $business_allotment
 * @property float $caregiver_allotment
 * @property float $system_allotment
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client|null $client
 * @property-read mixed $week
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\Billing\GatewayTransaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereAdjustment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCaregiverAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereSystemAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Payment extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;
    use ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    protected $table = 'payments';
    protected $guarded = ['id'];
    protected $appends = ['week'];
    protected $casts = [
        'amount' => 'float',
        'transaction_id' => 'int',
        'adjustment' => 'bool',
        'success' => 'bool',
        'system_allotment' => 'float',
    ];
    protected $week;

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /** @deprecated  */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(ClientInvoice::class, 'invoice_payments', 'payment_id', 'invoice_id')
            ->withPivot(['amount_applied']);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function transaction()
    {
        return $this->belongsTo(GatewayTransaction::class, 'transaction_id');
    }

    public function paymentMethod()
    {
        return $this->morphTo('payment_method');
    }

    ////////////////////////////////////
    //// Mutators
    ////////////////////////////////////

    public function getWeekAttribute()
    {
        if (!$this->week) {
            $shift = $this->shifts()->orderBy('checked_in_time', 'DESC')->first();
            if ($shift && $time = $shift->checked_in_time) {
                $time->setTimezone(Timezone::getTimezone($shift->business_id) ?: 'America/New_York');
                $this->week = (object) [
                    'start' => $time->setIsoDate($time->year, $time->weekOfYear)->toDateString(),
                    'end' => $time->setIsoDate($time->year, $time->weekOfYear, 7)->toDateString()
                ];
            }
        }

        return $this->week;
    }

    ////////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    /**
     * Return the amount of the payment allocated to the Ally Fee
     *
     * @return float
     */
    function getAllyFee(): float
    {
        return $this->system_allotment;
    }

    /**
     * Return the amount of the payment that has yet to be applied
     *
     * @return float
     */
    function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Return the amount of the payment that has been applied to invoices
     *
     * @return float
     */
    function getAmountApplied(): float
    {
        return (float) \DB::table('invoice_payments')->where('payment_id', $this->id)->sum('amount_applied');
    }

    /**
     * Return the amount of the payment that has yet to be applied
     *
     * @return float
     */
    function getAmountAvailable(): float
    {
        return subtract($this->amount, $this->getAmountApplied());
    }

    /**
     * Associate the corresponding payment method with this payment (Note: this does not save the payment method)
     *
     * @param \App\Billing\Contracts\ChargeableInterface $paymentMethod
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function setPaymentMethod(ChargeableInterface $paymentMethod)
    {
        if ($paymentMethod instanceof Model) {
            $this->paymentMethod()->associate($paymentMethod);
        } else {
            throw new PaymentMethodError("Unable to assign a non-model chargeable. Consult Payment::associateMethod");
        }
    }

    function getPaymentMethod(): ?ChargeableInterface
    {
        return $this->paymentMethod;
    }


    /**
     * Mark the payment as failed and emit the domain event
     *
     * @throws \Exception
     */
    function markFailed()
    {
        if (!$this->update(['success' => false])) {
            throw new \Exception('The payment could not be marked as failed.');
        }
        event(new PaymentFailed($this));
    }


    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker) : array
    {
        return [
            'notes' => $faker->sentence,
        ];
    }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('notes');
    }
}
