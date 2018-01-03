<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
 * @property int|null $success
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float $business_allotment
 * @property float $caregiver_allotment
 * @property float $system_allotment
 * @property-read \App\Business $business
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client|null $client
 * @property-read mixed $week
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\GatewayTransaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCaregiverAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereSystemAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Payment extends Model
{
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

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function getWeekAttribute()
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);
        $time = ($this->created_at instanceof Carbon) ? $this->created_at : Carbon::now();
        $time->subWeek(); // Use the previous week
        return (object) [
            'start' => $time->startOfWeek()->toDateString(),
            'end' => $time->endOfWeek()->toDateString()
        ];
        return null;
    }
}
