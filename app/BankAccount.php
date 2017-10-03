<?php

namespace App;

use App\Gateway\ChargeableInterface;
use App\Gateway\ACHDepositInterface;
use App\Gateway\ECSPayment;
use Crypt;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bank_accounts';
    protected $guarded = ['id'];
    protected $hidden = ['account_number', 'routing_number'];
    protected $appends = ['last_four'];
    protected static $accountTypes = [
        'checking',
        'savings'
    ];

    public function bankAccount()
    {
        return BankAccount::where('id', $this->bank_account_id)
            ->whereNull('user_id')
            ->first();
    }

    public function getLastFourAttribute()
    {
        return substr($this->account_number, -4);
    }

    public static function getAccountTypes()
    {
        return self::$accountTypes;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

    protected function ecsPaymentInstance()
    {
        $ecs = new ECSPayment();
        $billingNameArray = explode(' ', $this->name_on_account);

    }
}
