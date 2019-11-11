<?php

namespace App;

use App\Billing\CaregiverInvoice;
use App\Billing\Deposit;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\Billing\Payments\Methods\BankAccount;
use App\Caregiver1099;
use App\Businesses\Timezone;
use App\Contracts\BelongsToBusinessesInterface;
use App\Contracts\BelongsToChainsInterface;
use App\Contracts\HasPaymentHold as HasPaymentHoldInterface;
use App\Billing\Contracts\ReconcilableInterface;
use App\Contracts\HasTimezone;
use App\Contracts\UserRole;
use App\Exceptions\ExistingBankAccountException;
use App\Scheduling\ScheduleAggregator;
use App\Traits\BelongsToBusinesses;
use App\Traits\BelongsToChains;
use App\Traits\HasDefaultRates;
use App\Traits\HasPaymentHold;
use App\Traits\HasSSNAttribute;
use App\Traits\IsUserRole;
use App\Traits\ScrubsForSeeding;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Packages\MetaData\HasOwnMetaData;
use App\Traits\CanHaveEmptyEmail;
use App\Traits\CanHaveEmptyUsername;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

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
 * @property-read \App\Audit $auditTrail
 * @property-read \App\CaregiverAvailability $availability
 * @property-read \App\Billing\Payments\Methods\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BusinessChain[] $businessChains
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\CreditCard[] $creditCards
 * @property-read \App\RateCode|null $defaultFixedRate
 * @property-read \App\RateCode|null $defaultHourlyRate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Deposit[] $deposits
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payment[] $payments
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
 * @property-read mixed $created_at
 * @property-read mixed $masked_name
 * @property-read mixed $updated_at
 * @property-read \App\PhoneNumber $smsNumber
 * @property string|null $certification
 * @property string|null $deactivation_note
 * @property \Illuminate\Support\Carbon|null $application_date
 * @property \Illuminate\Support\Carbon|null $orientation_date
 * @property int|null $referral_source_id
 * @property int $smoking_okay
 * @property int $pets_dogs_okay
 * @property int $pets_cats_okay
 * @property int $pets_birds_okay
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverDayOff[] $daysOff
 * @property-read \App\DeactivationReason $deactivationReason
 * @property-read mixed $deactivation_reason_id
 * @property-read mixed $reactivation_date
 * @property-read mixed $setup_status
 * @property-read string $setup_url
 * @property-read mixed $status_alias_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\UserNotificationPreferences[] $notificationPreferences
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \App\ReferralSource|null $referralSource
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverRestriction[] $restrictions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SetupStatusHistory[] $setupStatusHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver query()
 * @property string|null $ethnicity
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver doesntHaveEmail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver hasEmail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver notOnboarded()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver onboarded()
 */
class Caregiver extends AuditableModel implements
    UserRole,
    ReconcilableInterface,
    HasPaymentHoldInterface,
    BelongsToChainsInterface,
    BelongsToBusinessesInterface,
    HasTimezone
{
    use IsUserRole, BelongsToBusinesses, BelongsToChains, Notifiable;
    use HasSSNAttribute, HasPaymentHold, HasOwnMetaData, HasDefaultRates, CanHaveEmptyEmail, CanHaveEmptyUsername;
    use ScrubsForSeeding;

    protected $table = 'caregivers';
    public $timestamps = false;
    public $hidden = ['ssn'];
    public $fillable = [
        'ssn',
        'bank_account_id',
        'title',
        'certification',
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
        'fixed_rate_id',
        'application_date',
        'orientation_date',
        'referral_source_id',
        'deactivation_note',
        'smoking_okay',
        'pets_dogs_okay',
        'pets_cats_okay',
        'pets_birds_okay',
        'ethnicity',
        'uses_ein_number',
    ];
    protected $appends = [ 'masked_ssn' ];
    protected $attributes = [];

    public $dates = [ 'onboarded', 'hire_date', 'deleted_at', 'application_date', 'orientation_date' ];

    /**
     * The notification classes related to this user role.
     *
     * @return array
     */
    public static $availableNotifications = [
        \App\Notifications\Caregiver\ShiftReminder::class,
        \App\Notifications\Caregiver\ClockInReminder::class,
        \App\Notifications\Caregiver\ClockOutReminder::class,
        \App\Notifications\Caregiver\VisitAccuracyCheck::class,
        \App\Notifications\Caregiver\CertificationExpiring::class,
        \App\Notifications\Caregiver\CertificationExpired::class,
        \App\Notifications\ChargePaymentNotification::class,
    ];

    ///////////////////////////////////////////
    /// Caregiver Setup Statuses
    ///////////////////////////////////////////

    const SETUP_NONE = null; // step 1
    const SETUP_CONFIRMED_PROFILE = 'confirmed_profile'; // step 2
    const SETUP_CREATED_ACCOUNT = 'created_account'; // step 3
    const SETUP_ADDED_PAYMENT = 'added_payment'; // step 4 (complete)
    
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

    /**
     * Get the businesses relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_caregivers');
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
        return $this->hasMany(CaregiverLicense::class)
                    ->orderBy("expires_at", 'ASC');
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

    public function referralSource()
    {
        return $this->belongsTo('App\ReferralSource');
    }

    /**
     * Get the caregiver days off relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function daysOff()
    {
        return $this->hasMany(CaregiverDayOff::class)
            ->where('start_date', '>', Carbon::today()->subWeek(1));
    }

    /**
     * Get the restrictions relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function restrictions()
    {
        return $this->hasMany(CaregiverRestriction::class);
    }

    /**
     * Get the caregiver1099 relations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caregiver1099s()
    {
        return $this->hasMany(Caregiver1099::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Get the account setup URL.
     *
     * @return string
     */
    public function getSetupUrlAttribute()
    {
        return route('setup.caregivers', ['token' => $this->getEncryptedKey()]);
    }


    public function getStatusAliasNameAttribute()
    {
        return $this->statusAlias ? $this->statusAlias->name : null;
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }

    /**
     * Set the caregiver's availability data
     *
     * @param array $data
     * @return \App\CaregiverAvailability|false
     */
    public function setAvailability(array $data)
    {
        $availability = $this->availability()->firstOrNew([]);
        $availability->fill($data);

        $saved = $availability->save();

        return $saved ? $availability : false;
    }

    /**
     * Set the caregiver's primary deposit account
     *
     * @param \App\Billing\Payments\Methods\BankAccount $account
     * @return \App\Billing\Payments\Methods\BankAccount|bool
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
        return (bool) $this->shifts()
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
    public function getActiveShift($client_id = null)
    {
        return $this->shifts()
            ->whereNull('checked_out_time')
            ->when($client_id, function ($query) use ($client_id) {
                return $query->where('client_id', $client_id);
            })
            ->first();
    }

    /**
     * If clocked in, return the active shift model
     *
     * @return \App\Shift|null
     */
    public function getActiveShifts()
    {
        return $this->shifts()->whereNull('checked_out_time')->get();
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

    /**
     * Override name to suffix title
     *
     * @return string
     */
    public function name(): string
    {
        return trim($this->user->name() . ' ' . $this->title);
    }

    /**
     * Override nameFirstLast to suffix title
     *
     * @return string
     */
    public function nameLastFirst(): string
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
     * @return \App\Billing\GatewayTransaction[]|\Illuminate\Support\Collection
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

    /**
     * Check if Caregiver has any scheduled shifts for the
     * specified Client.
     *
     * @param Client $client
     * @return boolean
     */
    public function hasScheduledShifts(Client $client) : bool
    {
        return $this->schedules()
            ->forClient($client)
            ->future($client->business->timezone)
            ->exists();
    }

    /**
     * Add Caregiver to office location if relationship does not exist.
     *
     * @param Business $business
     * @return bool
     */
    public function ensureBusinessRelationship(Business $business) : bool
    {
        if ($this->businesses()->where('business_id', $business->id)->exists()) {
            return true;
        }

        $this->businesses()->attach($business);

        return true;
    }

    /**
     * Add Caregiver to all office locations on a chain.
     *
     * @param \App\BusinessChain $chain
     * @return bool
     */
    public function ensureBusinessRelationships(BusinessChain $chain) : bool
    {
        foreach ($chain->businesses as $business) {
            $this->ensureBusinessRelationship($business);
        }

        return true;
    }

    /**
     * Get count of unpaid invoices.
     *
     * @return int
     */
    public function hasOpenInvoices()
    {
        return $this->hasMany(CaregiverInvoice::class)->whereRaw('amount_paid != amount')->count();
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * Filter only Caregivers that are on the schedule.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereScheduled($query)
    {
        return $query->whereHas('schedules');
    }

    /**
     * Filter only Caregivers that have been on the schedule
     * or have clocked in shifts
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereHasShiftsOrSchedules($query)
    {
        return $query->where(function ($query) {
            $query->whereHas('schedules')
                ->orWhereHas('shifts');
        });
    }

    /**
     * Get only the users who have not completed
     * the account setup wizard.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereNotSetup($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('setup_status', '<>', self::SETUP_ADDED_PAYMENT)
                ->orWhereNull('setup_status');
        });
    }

    /**
     * Get the date of the last shift between the Caregiver and
     * the given Client.
     *
     * @param Client $client
     * @return null|string
     */
    public function getLastServiceDate(Client $client) : ?string
    {
        $lastShift = Shift::forCaregiver($this->id)
            ->forClient($client->id)
            ->latest()
            ->first();
        
        if (empty($lastShift)) {
            return null;
        }

        return optional($lastShift->checked_in_time)->format('Y-m-d');
    }

    /**
     * Get the total number of hours the Caregiver has worked for
     * the given Client and between the given date range.
     *
     * @param null|integer $client
     * @param null|string $startDate
     * @param null|string $endDate
     * @return integer
     */
    public function totalServiceHours(?int $clientId = null, ?string $startDate = null, ?string $endDate = null) : int
    {
        $result = Shift::selectRaw('SUM(hours) as total_hours')
            ->forCaregiver($this->id)
            ->forClient($clientId)
            ->betweenDates($startDate, $endDate)
            ->whereNotNull('checked_out_time')
            ->whereConfirmed()
            ->first();

        if (empty($result)) {
            return 0;
        }

        return empty($result->total_hours) ? 0 : $result->total_hours;
    }

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
        $builder->whereHas('businesses', function ($q) use ($businessIds) {
            $q->whereIn('businesses.id', $businessIds);
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
        $chains = array_map(function ($chain) {
            return ($chain instanceof BusinessChain) ? $chain->id : $chain;
        }, (array) $chains);

        $builder->whereHas('businessChains', function ($q) use ($chains) {
            $q->whereIn('chain_id', $chains);
        });
    }

    /**
     * A query scope for filtering onboarded caregivers
     *
     * @param Builder $builder
     */
    public function scopeOnboarded(Builder $builder)
    {
        $builder->whereNotNull('onboarded')->orHas('shifts');
    }

    /**
     * A query scope for filtering caregivers who have not been onboarded
     *
     * @param Builder $builder
     */
    public function scopeNotOnboarded(Builder $builder)
    {
        $builder->whereNull('onboarded')->doesntHave('shifts');
    }

    /**

     * Gets a formatted list of audits.
     *
     * @return array
     */
    public function auditTrail()
    {
        $audits = Audit::where('new_values', 'like', '%"caregiver_id":' . $this->id . '%')
            ->orWhere(function ($q) {
                $q->whereIn('auditable_type', ['App\User', 'caregivers'])
                    ->where('auditable_id', $this->id);
            })
            ->get();
        return $audits;
    }

    /*
     *
     * Get the model's Timezone.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        // Attempt to get the timezone from the first business
        // they belong to.
        // TODO: this is faulty, Caregiver's should have a profile setting for timezone.
        if ($business = $this->businesses()->first()) {
            return Timezone::getTimezone($business->id);
        }

        return config('ally.local_timezone');
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'deactivation_note' => $fast ? null : $faker->sentence,
            'preferences' => $fast ? null : $faker->sentence,
            'w9_name' => $faker->name,
            'w9_business_name' => $faker->company,
            'w9_address' => $faker->streetAddress,
            'w9_employer_id_number' => $faker->randomNumber(9),
            'medicaid_id' => $faker->randomNumber(9),
        ];
    }
}
