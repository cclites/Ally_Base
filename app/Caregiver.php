<?php

namespace App;

use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Model;

class Caregiver extends Model
{
    use IsUserRole;

    protected $table = 'caregivers';
    public $timestamps = false;
    public $fillable = ['ssn', 'bank_account_id'];

    public function bankAccount()
    {
        return $this->bankAccounts->where('bank_account_id', $this->bank_account_id)->first();
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
}
