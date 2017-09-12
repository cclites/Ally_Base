<?php

namespace App;

use App\Exceptions\ExistingBankAccountException;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $table = 'businesses';
    protected $guarded = ['id'];

    public function setBankAccount(BankAccount $account)
    {
        if ($account->id) {
            throw new ExistingBankAccountException('setBankAccount only accepts new bank accounts.');
        }

        $account->user_id = null;
        if (!$account->save()) {
            throw new \Exception('Could not save the bank account to the database.');
        }

        $existingAccount = $this->bankAccount;
        $update = $this->update(['bank_account_id' => $account->id]);
        $this->load('bankAccount'); // reload bankAccount related model
        if ($update && $existingAccount) {
            $existingAccount->delete();
        }

        return $update;
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'business_clients')
            ->withTimestamps()
            ->withPivot([
                'business_fee',
                'default_payment_type',
                'default_payment_id',
                'backup_payment_type',
                'backup_payment_id',
                'created_at',
                'updated_at'
            ]);
    }

    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'business_caregivers')
            ->withPivot([
                'type'
            ]);
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

    public function users()
    {
        return $this->belongsToMany(OfficeUser::class, 'business_office_users');
    }
}
