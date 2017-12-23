<?php

namespace App;

use App\Contracts\ChargeableInterface;
use App\Exceptions\ExistingBankAccountException;
use App\Scheduling\ScheduleAggregator;
use Illuminate\Database\Eloquent\Model;

class Business extends Model implements ChargeableInterface
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

    public function paymentAccount()
    {
        return $this->belongsTo(BankAccount::class, 'payment_account_id');
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

    public function clientsUsingProviderPayment()
    {
        return $this->morphMany(Client::class, 'default_payment');
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

    /**
     * Return all scheduled events for a business between $start and $end
     *
     * @param $start
     * @param $end
     * @param bool $onlyStartTime
     * @return array
     */
    public function getEvents($start, $end, $onlyStartTime = false)
    {
        $aggregator = new ScheduleAggregator();
        foreach($this->schedules as $schedule) {
            $clientName = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $caregiverName = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            $title = $clientName . ' (' . $caregiverName . ')';
            $aggregator->add($title, $schedule);
        }

        return $aggregator->onlyStartTime($onlyStartTime)->events($start, $end);
    }

    /**
     * @param string $relation  Ex: paymentAccount
     * @return \App\BankAccount|null
     */
    public function getBankAccount($relation)
    {
        return $this->$relation;
    }

    /**
     * @param string $relation Ex: paymentAccount
     * @param \App\BankAccount $account
     * @return \App\BankAccount|bool
     * @throws \App\Exceptions\ExistingBankAccountException
     */
    public function setBankAccount($relation, BankAccount $account)
    {
        if ($account->id && $account->business_id != $this->id) {
            throw new ExistingBankAccountException('Bank account is owned by another user.');
        }
        $account->business_id = $this->id;

        $existing = $this->getBankAccount($relation);
        if ($existing && $existing->canBeMergedWith($account)) {
            if ($existing->mergeWith($account)) {
                return $existing;
            }
            return false;
        }

        if ($account->persistChargeable() && $this->$relation()->associate($account)->save()) {
            return $account;
        }
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD')
    {
        if ($this->paymentAccount) {
            return $this->paymentAccount->charge($amount, $currency);
        }
        return false;
    }

    /**
     * Determine if the existing record can be updated
     * This is used for the preservation of payment method on transaction history records
     *
     * @return bool
     */
    public function canBeMergedWith(ChargeableInterface $newPaymentMethod)
    {
        return false;
    }

    /**
     * Merge the existing record with the new values
     *
     * @return bool
     */
    public function mergeWith(ChargeableInterface $newPaymentMethod)
    {
        return false;
    }

    /**
     * Save a new Chargeable instance to the database
     */
    public function persistChargeable()
    {
        // Businesses should already be persisted
        return ($this->id > 0);
    }
}
