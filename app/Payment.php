<?php
namespace App;

use App\Businesses\Timezone;
use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

/**
 * App\Payment
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
 * @property-read \App\GatewayTransaction|null $transaction
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

    protected $table = 'payments';
    protected $guarded = ['id'];
    protected $appends = ['week'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

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

    ////////////////////////////////////
    //// Mutators
    ////////////////////////////////////

    public function getWeekAttribute()
    {
        $shift = $this->shifts()->orderBy('checked_in_time', 'DESC')->first();
        if ($shift && $time = $shift->checked_in_time) {
            $time->setTimezone(Timezone::getTimezone($shift->business_id) ?: 'America/New_York');
            return (object) [
                'start' => $time->setIsoDate($time->year, $time->weekOfYear)->toDateString(),
                'end' => $time->setIsoDate($time->year, $time->weekOfYear, 7)->toDateString()
            ];
        }
        return null;
    }

    ////////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////


}
