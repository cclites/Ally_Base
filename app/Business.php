<?php

namespace App;

use App\Contracts\ChargeableInterface;
use App\Exceptions\ExistingBankAccountException;
use App\Scheduling\ScheduleAggregator;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Business
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int|null $bank_account_id
 * @property int|null $active
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $country
 * @property string|null $phone1
 * @property string|null $phone2
 * @property float|null $default_commission_rate
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $timezone
 * @property int|null $payment_account_id
 * @property int $scheduling
 * @property float $mileage_rate
 * @property string $calendar_default_view
 * @property string $calendar_caregiver_filter
 * @property int $auto_confirm
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clientsUsingProviderPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \App\BankAccount|null $paymentAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PaymentQueue[] $upcomingPayments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAutoConfirm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCalendarCaregiverFilter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCalendarDefaultView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereDefaultCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMileageRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePaymentAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereScheduling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereZip($value)
 * @mixin \Eloquent
 */
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
