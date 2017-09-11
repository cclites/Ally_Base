<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bank_accounts';
    protected static $accountTypes = [
        'Checking',
        'Saving'
    ];

    public function bankAccount()
    {
        return BankAccount::where('id', $this->bank_account_id)
            ->whereNull('user_id')
            ->first();
    }

    public static function getAccountTypes()
    {
        return self::$accountTypes;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
