<?php


namespace App;

use App\Contracts\ChargeableInterface;
use App\Gateway\CreditCardPaymentInterface;
use Crypt;
use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model implements ChargeableInterface
{
    protected $table = 'credit_cards';
    protected $guarded = ['id'];
    protected $hidden = ['number'];
    protected $appends = ['last_four'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = Crypt::encrypt($value);
    }

    public function getNumberAttribute()
    {
        return empty($this->attributes['number']) ? null : Crypt::decrypt($this->attributes['number']);
    }

    public function getLastFourAttribute()
    {
        return substr($this->number, -4);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD')
    {
        $gateway = app()->make(CreditCardPaymentInterface::class);
        if ($this->user && $address = $this->user->addresses->where('type', 'billing')->first()) {
            $gateway->setBillingAddress($address);
        }
        if ($this->user && $phone = $this->user->phoneNumbers->where('type', 'billing')->first()) {
            $gateway->setBillingPhone($phone);
        }
        return $gateway->chargeCard($this, $amount, $currency);
    }
}
