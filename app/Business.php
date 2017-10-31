<?php

namespace App;

use App\Exceptions\ExistingBankAccountException;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $table = 'businesses';
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)
            ->orderBy('code');
    }

    public function allActivities()
    {
        return $this->activities->merge(Activity::whereNull('business_id')->get())->sortBy('code')->values();
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'business_caregivers')
            ->withPivot([
                'type',
                'default_rate'
            ]);
    }

    public function carePlans()
    {
        return $this->hasMany(CarePlan::class)->with('activities');
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

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function caregiverApplications()
    {
        return $this->hasMany(CaregiverApplication::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Set the Business' primary deposit account
     *
     * @param \App\BankAccount $account
     * @return bool
     * @throws \App\Exceptions\ExistingBankAccountException
     * @throws \Exception
     */
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

    /**
     * Find an activity by the activity code
     *
     * @param $code
     * @return \App\Activity|null
     */
    public function findActivity($code)
    {
        $activity = Activity::where(function ($q) {
            $q->where('business_id', $this->business_id)
                ->orWhereNull('business_id');
        })
            ->where('code', $code)
            ->first();
        return $activity;
    }
}
