<?php

namespace App;

use App\Contracts\ChargeableInterface;
use App\Gateway\ACHPaymentInterface;
use Crypt;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model implements ChargeableInterface
{
    protected $table = 'bank_accounts';
    protected $guarded = ['id'];
    protected $hidden = ['account_number', 'routing_number'];
    protected $appends = ['last_four'];
    protected static $accountTypes = [
        'checking',
        'savings'
    ];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function transactions()
    {
        return $this->morphMany(GatewayTransaction::class, 'method');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getLastFourAttribute()
    {
        return substr($this->account_number, -4);
    }

    public function setRoutingNumberAttribute($value)
    {
        $this->attributes['routing_number'] = Crypt::encrypt($value);
    }

    public function getRoutingNumberAttribute()
    {
        return empty($this->attributes['routing_number']) ? null : Crypt::decrypt($this->attributes['routing_number']);
    }

    public function setAccountNumberAttribute($value)
    {
        $this->attributes['account_number'] = Crypt::encrypt($value);
    }

    public function getAccountNumberAttribute()
    {
        return empty($this->attributes['account_number']) ? null : Crypt::decrypt($this->attributes['account_number']);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public static function getAccountTypes()
    {
        return self::$accountTypes;
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD')
    {
        $gateway = app()->make(ACHPaymentInterface::class);

        if ($this->user && $address = $this->user->addresses->where('type', 'billing')->first()) {
            $gateway->setBillingAddress($address);
        }
        elseif ($this->user && $address = $this->user->addresses->where('type', 'evv')->first()) {
            $gateway->setBillingAddress($address);
        }

        if ($this->user && $phone = $this->user->phoneNumbers->where('type', 'billing')->first()) {
            $gateway->setBillingPhone($phone);
        }
        elseif ($this->user && $phone = $this->user->phoneNumbers->where('type', 'evv')->first()) {
            $gateway->setBillingPhone($phone);
        }

        return $gateway->chargeAccount($this, $amount, $currency);
    }

}
