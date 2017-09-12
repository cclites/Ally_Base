<?php

namespace App;

use App\Exceptions\ExistingBankAccountException;
use App\Traits\IsUserRole;
use Crypt;
use Illuminate\Database\Eloquent\Model;

class Caregiver extends Model
{
    use IsUserRole;

    protected $table = 'caregivers';
    public $timestamps = false;
    public $fillable = ['ssn', 'bank_account_id'];

    public function setBankAccount(BankAccount $account)
    {
        if ($account->id && $account->user_id != $this->id) {
            throw new ExistingBankAccountException('Bank account is owned by another user.');
        }

        if (!$account->id) {
            if (!$this->bankAccounts()->save($account)) {
                throw new \Exception('Unable to save bank account to database.');
            }
        }

        return $this->update(['bank_account_id' => $account->id]);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upcomingPayments()
    {
        return $this->hasMany(PaymentQueue::class);
    }

    public function setSsnAttribute($value)
    {
        $this->attributes['ssn'] = Crypt::encrypt($value);
    }

    public function getSsnAttribute()
    {
        return empty($this->attributes['ssn']) ? null : Crypt::decrypt($this->attributes['ssn']);
    }
}
