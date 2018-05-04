<?php

namespace App;

use App\Confirmations\Confirmation;
use App\Contracts\CanBeConfirmedInterface;
use App\Contracts\HasPaymentHold;
use App\Contracts\ReconcilableInterface;
use App\Contracts\UserRole;
use App\Exceptions\ExistingBankAccountException;
use App\Mail\CaregiverConfirmation;
use App\Scheduling\ScheduleAggregator;
use App\Traits\IsUserRole;
use Crypt;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Caregiver
 *
 * @property int $id
 * @property null|string $ssn
 * @property int|null $bank_account_id
 * @property string|null $title
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $hire_date
 * @property string|null $gender
 * @property \Carbon\Carbon|null $onboarded
 * @property string|null $misc
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \App\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverLicense[] $licenses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PaymentQueue[] $upcomingPayments
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereMisc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereOnboarded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereSsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereTitle($value)
 * @mixin \Eloquent
 * @property-read mixed $active
 */
class Caregiver extends Model implements UserRole, CanBeConfirmedInterface, ReconcilableInterface, HasPaymentHold, Auditable
{
    use IsUserRole;
    use \App\Traits\HasPaymentHold;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'caregivers';
    public $timestamps = false;
    public $hidden = ['ssn'];
    public $fillable = [
        'ssn',
        'bank_account_id',
        'title',
        'hire_date',
        'gender',
        'onboarded',
        'misc',
        'preferences',
        'import_identifier',
        'preferences',
        'w9_name',
        'w9_business_name',
        'w9_tax_classification',
        'w9_llc_type',
        'w9_exempt_payee_code',
        'w9_exempt_fatca_reporting_code',
        'w9_address',
        'w9_city_state_zip',
        'w9_account_numbers',
        'w9_employer_id_number'
    ];

    public $dates = ['onboarded', 'hire_date', 'deleted_at'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_caregivers');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_caregivers')
                    ->withTimestamps()
                    ->withPivot([
                        'caregiver_hourly_rate',
                        'caregiver_daily_rate',
                        'provider_hourly_fee',
                        'provider_daily_fee',
                    ]);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function licenses()
    {
        return $this->hasMany(CaregiverLicense::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upcomingPayments()
    {
        return $this->hasMany(PaymentQueue::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * A Caregiver has many Future Schedules.
     *
     * @return void
     */
    public function futureSchedules()
    {
        return $this->schedules()
            ->where('starts_at', '>', Carbon::now());
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Encrypt ssn on entry
     *
     * @param $value
     */
    public function setSsnAttribute($value)
    {
        $this->attributes['ssn'] = Crypt::encrypt($value);
    }

    /**
     * Decrypt ssn on retrieval
     *
     * @return null|string
     */
    public function getSsnAttribute()
    {
        return empty($this->attributes['ssn']) ? null : Crypt::decrypt($this->attributes['ssn']);
    }

    /**
     * Encrypt ssn on entry
     *
     * @param $value
     */
    public function setw9SsnAttribute($value)
    {
        $this->setSsnAttribute($value);
    }

    /**
     * Decrypt ssn on retrieval
     *
     * @return null|string
     */
    public function getw9SsnAttribute()
    {
        return $this->getSsnAttribute();
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Retrieve the fake email address for a caregiver that does not have an email address.
     * This should always be a domain in our control that drops the emails to prevent leaking of sensitive information and bounces.
     *
     * @return string
     */
    public function getAutoEmail()
    {
        return $this->id . '@noemail.allyms.com';
    }

    /**
     * Set the generated fake email address for a caregiver that does not have an email address.
     *
     * @return $this
     */
    public function setAutoEmail()
    {
        $this->email = $this->getAutoEmail();
        return $this;
    }

    /**
     * Set the caregiver's primary deposit account
     *
     * @param \App\BankAccount $account
     * @return \App\BankAccount|bool
     * @throws \App\Exceptions\ExistingBankAccountException
     */
    public function setBankAccount(BankAccount $account)
    {
        if ($account->id && $account->user_id != $this->id) {
            throw new ExistingBankAccountException('Bank account is owned by another user.');
        }
        $account->user_id = $this->id;

        $existing = $this->bankAccount;
        if ($existing && $existing->canBeMergedWith($account)) {
            if ($existing->mergeWith($account)) {
                return $existing;
            }
            return false;
        }

        if ($account->persistChargeable() && $this->bankAccount()->associate($account)->save()) {
            return $account;
        }
    }

    /**
     * Check if the caregiver is currently clocked in to a shift
     *
     * @param null $client_id
     * @return bool
     */
    public function isClockedIn($client_id = null)
    {
        return $this->shifts()
            ->whereNull('checked_out_time')
            ->when($client_id, function ($query) use ($client_id) {
                return $query->where('client_id', $client_id);
            })
            ->exists();
    }

    /**
     * If clocked in, return the active shift model
     *
     * @return \App\Shift|null
     */
    public function getActiveShift()
    {
        return $this->shifts()->whereNull('checked_out_time')->first();
    }

    /**
     * Checks if Caregiver has a shift that is still clocked in.
     *
     * @return boolean
     */
    public function hasActiveShift()
    {
        return $this->shifts()->whereNull('checked_out_time')->exists();
    }

    /**
     * Unassign all Caregiver's schedules from now on. 
     *
     * @return void
     */
    public function unassignFromFutureSchedules()
    {
        $this->schedules()
             ->where('starts_at', '>', Carbon::now())
             ->update(['caregiver_id' => null]);
    }

    /**
     * Aggregate schedules for this caregiver and return an array of events
     *
     * @param string|\DateTime $start
     * @param string|\DateTime $end
     *
     * @return array
     */
    public function getEvents($start, $end)
    {
        $aggregator = new ScheduleAggregator();
        foreach ($this->schedules as $schedule) {
            $title = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $aggregator->add($title, $schedule);
        }

        return $aggregator->events($start, $end);
    }

    public function sendConfirmationEmail()
    {
        $confirmation = new Confirmation($this);
        $confirmation->touchTimestamp();
        \Mail::to($this->email)->send(new CaregiverConfirmation($this, $this->businesses()->first()));
    }

    /**
     * Override name to suffix title
     *
     * @return string
     */
    public function name()
    {
        return trim($this->user->name() . ' ' . $this->title);
    }

    /**
     * Override nameFirstLast to suffix title
     *
     * @return string
     */
    public function nameLastFirst()
    {
        return trim($this->user->nameLastFirst() . ' ' . $this->title);
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
                                 ->leftJoin('bank_accounts', function ($q) {
                                     $q->on('bank_accounts.id', '=', 'gateway_transactions.method_id')
                                       ->where('gateway_transactions.method_type', BankAccount::class);
                                 })
                                 ->whereHas('deposit', function ($q) {
                                     $q->where('caregiver_id', $this->id);
                                 })
                                 ->orWhere('bank_accounts.user_id', $this->id);
    }

    /**
     * Get all gateway transactions that relate to this caregiver
     *
     * @return \App\GatewayTransaction[]|\Illuminate\Support\Collection
     */
    public function getAllTransactions()
    {
        return $this->allTransactionsQuery()
                    ->orderBy('created_at')
                    ->get();
    }
}
