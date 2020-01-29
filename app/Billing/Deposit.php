<?php
namespace App\Billing;

use App\AuditableModel;
use App\Business;
use App\BusinessChain;
use App\Caregiver;
use App\Contracts\BelongsToBusinessesInterface;
use App\Contracts\ContactableInterface;
use App\Events\DepositFailed;
use App\Shift;
use App\Traits\BelongsToOneBusiness;

/**
 * App\Billing\Deposit
 *
 * @property int $id
 * @property string $deposit_type
 * @property int|null $caregiver_id
 * @property int|null $business_id
 * @property int $chain_id
 * @property float|null $amount
 * @property string|null $transaction_id
 * @property string|null $transaction_code
 * @property int $adjustment
 * @property string|null $notes
 * @property int|null $success
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read mixed $week
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\Billing\GatewayTransaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereAdjustment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereDepositType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\BusinessInvoice[] $businessInvoices
 * @property-read int|null $business_invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\CaregiverInvoice[] $caregiverInvoices
 * @property-read int|null $caregiver_invoices_count
 * @property-read \App\BusinessChain|null $chain
 * @property-read int|null $shifts_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Deposit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Deposit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Deposit query()
 */
class Deposit extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $table = 'deposits';
    protected $guarded = ['id'];
    protected $appends = ['week'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function transaction()
    {
        return $this->belongsTo(GatewayTransaction::class, 'transaction_id');
    }

    /** @deprecated */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'deposit_shifts');
    }

    public function businessInvoices()
    {
        return $this->morphedByMany(BusinessInvoice::class, 'invoice', 'invoice_deposits')
            ->withPivot(['amount_applied']);
    }

    public function caregiverInvoices()
    {
        return $this->morphedByMany(CaregiverInvoice::class, 'invoice', 'invoice_deposits')
            ->withPivot(['amount_applied']);
    }

    public function chain(){
        return $this->belongsTo(BusinessChain::class, 'chain_id', 'id');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getWeekAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        $date = $this->created_at->copy()->subWeek();
        $start = $date->setIsoDate($date->year, $date->weekOfYear);
        $end = $start->copy()->addDays(6);
        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString()
        ];
    }


    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function getRecipient(): ContactableInterface
    {
        return $this->caregiver ?? $this->business ?? new Business();
    }

    /**
     * Get the total deposit amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    /**
     * Return the amount of the deposit that has been applied to invoices
     *
     * @return float
     */
    function getAmountApplied(): float
    {
        return (float) \DB::table('invoice_deposits')->where('deposit_id', $this->id)->sum('amount_applied');
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
     * Mark the deposit as failed and emit the domain event
     *
     * @throws \Exception
     */
    function markFailed()
    {
        if (!$this->update(['success' => false])) {
            throw new \Exception('The deposit could not be marked as failed.');
        }
        event(new DepositFailed($this));
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('notes');
    }

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'notes' => $faker->sentence,
        ];
    }
}
