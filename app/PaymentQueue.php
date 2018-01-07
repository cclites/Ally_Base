<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PaymentQueue
 *
 * @property int $id
 * @property int $client_id
 * @property int|null $caregiver_id
 * @property int|null $business_id
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property float|null $amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $process_at
 * @property float $business_allotment
 * @property float $caregiver_allotment
 * @property float $system_allotment
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reference
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereBusinessAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereCaregiverAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereProcessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereSystemAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentQueue extends Model
{
    protected $table = 'payment_queue';

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

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

    public function reference()
    {
        return $this->morphTo();
    }

    public function method()
    {
        return $this->morphTo();
    }

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
