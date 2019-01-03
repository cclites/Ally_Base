<?php

namespace App;

use App\Confirmations\Confirmation;
use App\Contracts\BelongsToChainsInterface;
use App\Contracts\CanBeConfirmedInterface;
use App\Contracts\HasPaymentHold as HasPaymentHoldInterface;
use App\Contracts\ReconcilableInterface;
use App\Contracts\UserRole;
use App\Exceptions\ExistingBankAccountException;
use App\Mail\CaregiverConfirmation;
use App\Scheduling\ScheduleAggregator;
use App\Traits\BelongsToBusinesses;
use App\Traits\BelongsToChains;
use App\Traits\HasDefaultRates;
use App\Traits\HasPaymentHold;
use App\Traits\HasSSNAttribute;
use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Packages\MetaData\HasOwnMetaData;

/**
 * App\Caregiver
 *
 * @property int $id
 * @property null|string $ssn
 * @property int|null $bank_account_id
 * @property string|null $title
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $hire_date
 * @property \Carbon\Carbon|null $onboarded
 * @property string|null $misc
 * @property string|null $preferences
 * @property string|null $import_identifier
 * @property string|null $w9_name
 * @property string|null $w9_business_name
 * @property string|null $w9_tax_classification
 * @property string|null $w9_llc_type
 * @property string|null $w9_exempt_payee_code
 * @property string|null $w9_exempt_fatca_reporting_code
 * @property string|null $w9_address
 * @property string|null $w9_city_state_zip
 * @property string|null $w9_account_numbers
 * @property string|null $w9_employer_id_number
 * @property string|null $medicaid_id
 * @property int|null $hourly_rate_id
 * @property int|null $fixed_rate_id
 * @property-read \App\Address $address
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\CaregiverAvailability $availability
 * @property-read \App\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BusinessChain[] $businessChains
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \App\RateCode|null $defaultFixedRate
 * @property-read \App\RateCode|null $defaultHourlyRate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $active
 * @property mixed $avatar
 * @property-read \App\Business[]|\Illuminate\Database\Eloquent\Collection $businesses
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $gender
 * @property-read mixed $in_active_at
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $username
 * @property null|string $w9_ssn
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverLicense[] $licenses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverMeta[] $meta
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \App\PaymentHold $paymentHold
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \App\PhoneNumber $phoneNumber
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $skills
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver orderByName($direction = 'ASC')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereEmail($email = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereFixedRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereHourlyRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereImportIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereMedicaidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereMeta($key, $delimiter = null, $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereMisc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereName($firstname = null, $lastname = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereOnboarded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver wherePreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereSsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9AccountNumbers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9Address($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9BusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9CityStateZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9EmployerIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9ExemptFatcaReportingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9ExemptPayeeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9LlcType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereW9TaxClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver withMeta()
 * @mixin \Eloquent
 * @property-read string $masked_ssn
 */
class Caregiver extends AuditableModel implements UserRole, CanBeConfirmedInterface, ReconcilableInterface, HasPaymentHoldInterface, BelongsToChainsInterface
{
    use IsUserRole, BelongsToBusinesses, BelongsToChains;
    use HasSSNAttribute, HasPaymentHold, HasOwnMetaData, HasDefaultRates;

    protected $table = 'caregivers';
    public $timestamps = false;
    public $hidden = ['ssn'];
    public $fillable = [
        'ssn',
        'bank_account_id',
        'title',
        'hire_date',
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
        'w9_employer_id_number',
        'medicaid_id',
        'hourly_rate_id',
        'fixed_rate_id'
    ];
    protected $appends = ['masked_ssn'];

    public $dates = ['onboarded', 'hire_date', 'deleted_at'];

    /**
     * The notification classes related to this user role.
     *
     * @return array
     */
    public static $availableNotifications = [
        \App\Notifications\Caregiver\ShiftReminder::class, // TODO: implement trigger
        \App\Notifications\Caregiver\ClockInReminder::class, // TODO: implement trigger
        \App\Notifications\Caregiver\ClockOutReminder::class, // TODO: implement trigger
        \App\Notifications\Caregiver\VisitAccuracyCheck::class, // TODO: implement trigger
        \App\Notifications\Caregiver\CertificationExpiring::class, // TODO: implement trigger
        \App\Notifications\Caregiver\CertificationExpired::class, // TODO: implement trigger
    ];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function address()
    {
        return $this->hasOne(Address::class, 'user_id')
                    ->where('type', 'home');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function businessChains()
    {
        return $this->belongsToMany(BusinessChain::class, 'chain_caregivers', 'caregiver_id', 'chain_id')
            ->withTimestamps();
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_caregivers')
                    ->withTimestamps()
                    ->withPivot([
                        'caregiver_hourly_id',
                        'caregiver_hourly_rate',
                        'caregiver_fixed_id',
                        'caregiver_fixed_rate',
                        'client_hourly_id',
                        'client_hourly_rate',
                        'client_fixed_id',
                        'client_fixed_rate',
                        'provider_hourly_id',
                        'provider_hourly_fee',
                        'provider_fixed_id',
                        'provider_fixed_fee',
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

    public function phoneNumber()
    {
        return $this->hasOne(PhoneNumber::class, 'user_id', 'id')
                    ->where('type', 'primary');
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

    public function availability()
    {
        return $this->hasOne(CaregiverAvailability::class, 'id');
    }

    public function skills()
    {
        return $this->belongsToMany(Activity::class, 'caregiver_skills');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Backwards compatibility with old relationship, return a collection of all businesses through chains
     * @return \App\Business[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBusinessesAttribute()
    {
        foreach($this->businessChains as $chain) {
            $collection = $chain->businesses;
            $businesses = isset($businesses) ? $businesses->merge($collection) : $collection;
        }
        return $businesses ?? collect();
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Set the caregiver's availability data
     *
     * @param array $data
     * @return \App\CaregiverAvailability|false
     */
    public function setAvailability(array $data) {
        $availability = $this->availability()->firstOrNew([]);
        $availability->fill($data);
        return $availability->save() ? $availability : false;
    }

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
             ->where('starts_at', '>=', Carbon::today())
             ->doesntHave('shifts')
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

    public function sendConfirmationEmail(BusinessChain $businessChain = null)
    {
        if (!$businessChain) $businessChain = $this->businessChains()->first();
        $confirmation = new Confirmation($this);
        $confirmation->touchTimestamp();
        \Mail::to($this->email)->send(new CaregiverConfirmation($this, $businessChain));
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

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return $this->businesses->pluck('id')->toArray();
    }

    /**
     * Return an array of business chain IDs the entity is attached to
     *
     * @return array
     */
    public function getChainIds()
    {
        return $this->businessChains->pluck('id')->toArray();
    }

    /**
     * Determine if the caregiver is assigned to the given client.
     *
     * @param \App\Client|int $client
     * @return bool
     */
    public function belongsToClient($client)
    {
        if (is_object($client)) {
            $client = $client->id;
        }

        return $this->clients()->where('client_id', $client)->exists();
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * A query scope for filtering results by related business IDs
     * Note: Use forAuthorizedBusinesses in controllers
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->whereHas('businessChains.businesses', function($q) use ($businessIds) {
            $q->whereIn('id', $businessIds);
        });
    }

    /**
     * A query scope for filtering results by related business chains
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int|\App\BusinessChain|array $chains
     * @return void
     */
    public function scopeForChains(Builder $builder, $chains)
    {
        $chains = array_map(function($chain) {
            return ($chain instanceof BusinessChain) ? $chain->id : $chain;
        }, (array) $chains);

        $builder->whereHas('businessChains', function($q) use ($chains) {
            $q->whereIn('chain_id', $chains);
        });
    }
}
