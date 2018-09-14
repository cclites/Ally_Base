<?php

namespace App;

use App\Contracts\ChargeableInterface;
use App\Contracts\HasPaymentHold;
use App\Contracts\ReconcilableInterface;
use App\Exceptions\ExistingBankAccountException;
use App\Scheduling\ScheduleAggregator;
use App\Traits\HasAllyFeeTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

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
 * @property int $ask_on_confirm
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAskOnConfirm($value)
 */
class Business extends Model implements ChargeableInterface, ReconcilableInterface, HasPaymentHold, Auditable
{
    use \App\Traits\HasPaymentHold;
    use HasAllyFeeTrait;
    use \OwenIt\Auditing\Auditable;

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

    public function activeClients()
    {
        return $this->clients()->whereHas('user', function($q) { $q->where('active', 1); });
    }

    public function clientsUsingProviderPayment()
    {
        return $this->morphMany(Client::class, 'default_payment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'business_caregivers')
            ->withPivot([
                'type',
                'default_rate'
            ]);
    }

    public function activeCaregivers()
    {
        return $this->caregivers()->whereHas('user', function($q) { $q->where('active', 1); });
    }

    public function carePlans()
    {
        return $this->hasMany(CarePlan::class)->with('activities');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function exceptions() {
        return $this->hasMany(SystemException::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentHold()
    {
        return $this->hasOne(PaymentHold::class, 'business_id');
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

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function caregiverApplications()
    {
        return $this->hasMany(CaregiverApplication::class);
    }

    public function chargedTransactions()
    {
        if ($this->paymentAccount) {
            return $this->paymentAccount->chargedTransactions();
        }
        return $this->morphMany(GatewayTransaction::class, 'method');
    }

    /**
     * Get the custom questions relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
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
            $q->where('business_id', $this->id)
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
     * Refund a previously charged transaction
     *
     * @param \App\GatewayTransaction $transaction
     * @param $amount
     * @return \App\GatewayTransaction|false
     */
    public function refund(GatewayTransaction $transaction, $amount)
    {
        if ($this->paymentAccount) {
            return $this->paymentAccount->refund($transaction, $amount);
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

    /**
     * Prepare a query for all gateway transactions that relate to this model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allTransactionsQuery()
    {
        return GatewayTransaction::select('gateway_transactions.*')
                                 ->with('lastHistory')
                                 ->leftJoin('bank_accounts', function($q) {
                                     $q->on('bank_accounts.id', '=', 'gateway_transactions.method_id')
                                       ->where('gateway_transactions.method_type', BankAccount::class);
                                 })
                                 ->whereHas('deposit', function ($q) {
                                     $q->where('business_id', $this->id)
                                       ->whereNull('caregiver_id');
                                 })
                                 ->orWhere('bank_accounts.business_id', $this->id);
    }

    /**
     * Get all gateway transactions that relate to this client
     *
     * @return \App\GatewayTransaction[]|\Illuminate\Support\Collection
     */
    public function getAllTransactions()
    {
        return $this->allTransactionsQuery()
                    ->orderBy('created_at')
                    ->get();
    }

    /**
     * Check to see if a user with the same name or email has already been entered
     *
     * @param string $firstname
     * @param string $lastname
     * @param string|null $email
     * @param string|null $role
     *
     * @return false|string   Returns the matching field or false for no duplicates
     */
    public function checkForDuplicateUser($firstname, $lastname, $email = null, $role = null)
    {
        $ids = [];
        if (!$role || $role === 'caregiver') {
            $ids = array_merge($ids, $this->caregivers()->pluck('caregiver_id')->toArray());
        }
        if (!$role || $role === 'client') {
            $ids = array_merge($ids, $this->clients()->pluck('id')->toArray());
        }

        if ($email) {
            $matching = User::where('email', $email)->get();
            if ($matching->whereIn('id', $ids)->count()) {
                return 'email';
            }
        }

        $matching = User::where('firstname', $firstname)
            ->where('lastname', $lastname)
            ->get();
        if ($matching->whereIn('id', $ids)->count()) {
            return 'name';
        }

        return false;
    }

    /**
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage()
    {
        return (float) config('ally.bank_account_fee');
    }

    /**
     * Gets list of all the business' caregivers with attached clients
     * in simple array.  Intended for smart dropdowns.
     *
     * @return array
     */
    public function caregiverClientList()
    {
        return $this->caregivers()->with('clients')->get()->map(function ($cg) {
            return [
                'id' => $cg->id,
                'name' => $cg->nameLastFirst,
                'clients' => $cg->clients->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->nameLastFirst,
                        'caregiver_hourly_rate' => $c->pivot->caregiver_hourly_rate,
                        'provider_hourly_fee' => $c->pivot->provider_hourly_fee,
                    ];
                }),
            ];
        });
    }

    /**
     * Get a simple list of caregiver names and ids.
     *
     * @param boolean $lastFirst
     * @param boolean $activeOnly
     * @return void
     */
    public function caregiverList($lastFirst = true, $activeOnly = false)
    {
        $sort = $lastFirst ? 'nameLastFirst' : 'name';

        $query = $activeOnly ? $this->activeCaregivers() : $this->caregivers();

        return $query->get()
            ->sortBy($sort, SORT_NATURAL|SORT_FLAG_CASE)->map(function ($item) use($lastFirst) {
                return [
                    'id' => $item->id,
                    'name' => $lastFirst ? $item->nameLastFirst : $item->name,
                ];
        })->values();
    }

    /**
     * Get a simple list of client names and ids.
     *
     * @param boolean $lastFirst
     * @param boolean $activeOnly
     * @return void
     */
    public function clientList($lastFirst = true, $activeOnly = false)
    {
        $sort = $lastFirst ? 'nameLastFirst' : 'name';

        $query = $activeOnly ? $this->activeClients() : $this->clients();

        return $query->get()
            ->sortBy($sort, SORT_NATURAL|SORT_FLAG_CASE)->map(function ($item) use($lastFirst) {
                return [
                    'id' => $item->id,
                    'name' => $lastFirst ? $item->nameLastFirst : $item->name,
                ];
        })->values();
    }
}
