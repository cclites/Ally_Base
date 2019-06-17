<?php

namespace App;

use App\Billing\ClientPayer;
use App\Billing\ClientRate;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\PaymentMethodType;
use App\Businesses\Timezone;
use App\Contracts\BelongsToBusinessesInterface;
use App\Billing\Contracts\ChargeableInterface;
use App\Contracts\HasAllyFeeInterface;
use App\Contracts\HasPaymentHold;
use App\Billing\Contracts\ReconcilableInterface;
use App\Contracts\UserRole;
use App\Scheduling\ScheduleAggregator;
use App\Traits\BelongsToOneBusiness;
use App\Traits\HasAllyFeeTrait;
use App\Traits\HasDefaultRates;
use App\Traits\HasPaymentHold as HasPaymentHoldTrait;
use App\Traits\HasSSNAttribute;
use App\Traits\IsUserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notifiable;
use Packages\MetaData\HasOwnMetaData;
use App\Traits\CanHaveEmptyEmail;
use App\Billing\ClientAuthorization;
use App\Traits\CanHaveEmptyUsername;

/**
 * App\Client
 *
 * @property int $id
 * @property int $business_id
 * @property float|null $business_fee
 * @property string|null $default_payment_type
 * @property string|null $default_payment_id
 * @property string|null $backup_payment_type
 * @property string|null $backup_payment_id
 * @property string $client_type
 * @property mixed|null $ssn
 * @property string|null $agreement_status
 * @property string|null $deleted_at
 * @property float|null $fee_override
 * @property float $max_weekly_hours
 * @property \Carbon\Carbon|null $inquiry_date
 * @property \Carbon\Carbon|null $service_start_date
 * @property string|null $referral
 * @property string|null $diagnosis
 * @property int|null $ambulatory
 * @property string|null $import_identifier
 * @property string|null $ltci_name
 * @property string|null $ltci_address
 * @property string|null $ltci_city
 * @property string|null $ltci_state
 * @property string|null $ltci_zip
 * @property string|null $ltci_policy
 * @property string|null $ltci_claim
 * @property string|null $ltci_phone
 * @property string|null $ltci_fax
 * @property string|null $hospital_name
 * @property string|null $hospital_number
 * @property string|null $medicaid_id
 * @property string|null $medicaid_diagnosis_codes
 * @property string|null $client_type_descriptor
 * @property int $receive_summary_email
 * @property int|null $referral_source_id
 * @property int|null $onboarding_step
 * @property int|null $hourly_rate_id
 * @property int|null $fixed_rate_id
 * @property int|null $case_manager_id
 * @property string|null $hic;
 * @property string|null $travel_directions;
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $created_by;
 * @property int|null $updated_by;
 * @property string|null $disaster_code_plan;
 * @property string|null $disaster_planning;
 * @property int|null $caregiver_1099;
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Model|ChargeableInterface $backupPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\BankAccount[] $bankAccounts
 * @property-read \App\Business $business
 * @property-read \App\CareDetails $careDetails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\CreditCard[] $creditCards
 * @property-read \App\RateCode|null $defaultFixedRate
 * @property-read \App\RateCode|null $defaultHourlyRate
 * @property-read \Illuminate\Database\Eloquent\Model|ChargeableInterface $defaultPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read \App\Address $evvAddress
 * @property-read \App\PhoneNumber $evvPhone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientExcludedCaregiver[] $excludedCaregivers
 * @property-read mixed $active
 * @property mixed $avatar
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $gender
 * @property-read mixed $in_active_at
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $payment_type
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientGoal[] $goals
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientMedication[] $medications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientMeta[] $meta
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientNarrative[] $narrative
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientAgreementStatusHistory[] $agreementStatusHistory
 * @property-read \App\PaymentHold $paymentHold
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \App\ClientPreferences $preferences
 * @property-read \App\ClientReferralServiceAgreement $referralServiceAgreement
 * @property-read \App\ReferralSource|null $referralSource
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\User $user
 * @property-read \App\User $case_manager
 * @property-read \App\User $creator
 * @property-read \App\User $updator
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client orderByName($direction = 'ASC')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereAmbulatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBackupPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBackupPaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBusinessFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereClientTypeDescriptor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDefaultPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDefaultPaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDiagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereEmail($email = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereFeeOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereFixedRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereHospitalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereHospitalNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereHourlyRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereImportIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereInquiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciClaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereLtciZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereMaxWeeklyHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereMedicaidDiagnosisCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereMedicaidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereMeta($key, $delimiter = null, $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereName($firstname = null, $lastname = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereOnboardStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereOnboardingStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereReceiveSummaryEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereReferral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereReferralSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereServiceStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereSsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereCaseManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereHIC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereTravelDirections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDisasterPlanCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDisasterPlanning($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereCaregiver1099($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client withMeta()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read string $masked_ssn
 * @property null|string $w9_ssn
 * @property-read \App\OfficeUser|null $caseManager
 * @property-read mixed $masked_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientPayer[] $payers
 * @property-read \App\Billing\ClientPayer $primaryPayer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientRate[] $rates
 * @property-read \App\PhoneNumber $smsNumber
 * @property string|null $hic
 * @property string|null $travel_directions
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $disaster_code_plan
 * @property string|null $disaster_planning
 * @property string|null $caregiver_1099
 * @property string|null $discharge_reason
 * @property string|null $discharge_condition
 * @property string|null $discharge_goals_eval
 * @property string|null $discharge_disposition
 * @property string|null $discharge_internal_notes
 * @property int|null $sales_person_id
 * @property int|null $quickbooks_customer_id
 * @property-read \App\Address $billingAddress
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientContact[] $contacts
 * @property-read \App\DeactivationReason $deactivationReason
 * @property-read mixed $deactivation_reason_id
 * @property-read mixed $last_service_date
 * @property-read mixed $reactivation_date
 * @property-read mixed $setup_status
 * @property-read string $setup_url
 * @property-read mixed $status_alias_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\UserNotificationPreferences[] $notificationPreferences
 * @property-read \App\QuickbooksCustomer|null $quickbooksCustomer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientAuthorization[] $serviceAuthorizations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SetupStatusHistory[] $setupStatusHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client doesntHaveEmail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client forChain($chainId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client hasEmail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client query()
 */
class Client extends AuditableModel implements
    UserRole,
    ReconcilableInterface,
    HasPaymentHold,
    HasAllyFeeInterface,
    BelongsToBusinessesInterface
{
    use IsUserRole, BelongsToOneBusiness, Notifiable;
    use HasSSNAttribute, HasPaymentHoldTrait, HasAllyFeeTrait, HasOwnMetaData, HasDefaultRates;
    use CanHaveEmptyEmail, CanHaveEmptyUsername;

    protected $table = 'clients';
    public $timestamps = false;
    public $hidden = ['ssn'];
    public $dates = ['service_start_date', 'inquiry_date'];
    public $fillable = [
        'business_id',
        'business_fee',
        'client_type',
        'default_payment_type',
        'default_payment_id',
        'backup_payment_type',
        'backup_payment_id',
        'ssn',
        'onboard_status',
        'fee_override',
        'max_weekly_hours',
        'inquiry_date',
        'service_start_date',
        'referral',
        'diagnosis',
        'ambulatory',
        'import_identifier',
        'hospital_name',
        'hospital_number',
        'ltci_name',
        'ltci_address',
        'ltci_city',
        'ltci_state',
        'ltci_zip',
        'ltci_policy',
        'ltci_claim',
        'ltci_phone',
        'ltci_fax',
        'medicaid_id',
        'medicaid_diagnosis_codes',
        'client_type_descriptor',
        'receive_summary_email',
        'onboarding_step',
        'referral_source_id',
        'qb_customer_id',
        'hourly_rate_id',
        'fixed_rate_id',
        'discharge_reason',
        'discharge_condition',
        'discharge_goals_eval',
        'discharge_disposition',
        'discharge_internal_notes',
        'hic',
        'travel_directions',
        'created_by',
        'updated_by',
        'disaster_code_plan',
        'disaster_planning',
        'caregiver_1099',
        'case_manager_id',
        'discharge_reason',
        'discharge_condition',
        'discharge_goals_eval',
        'discharge_disposition',
        'discharge_internal_notes',
        'sales_person_id',
        'agreement_status',
        'quickbooks_customer_id',
    ];

    ///////////////////////////////////////////
    /// Client Agreement Statuses
    ///////////////////////////////////////////

    const NEEDS_AGREEMENT = 'needs_agreement';
    const SIGNED_ELECTRONICALLY = 'electronic';
    const SIGNED_PAPER = 'paper';

    ///////////////////////////////////////////
    /// Client Setup Statuses
    ///////////////////////////////////////////

    const SETUP_NONE = null; // step 1
    const SETUP_ACCEPTED_TERMS = 'accepted_terms'; // step 2
    const SETUP_CREATED_ACCOUNT = 'created_account'; // step 3
    const SETUP_ADDED_PAYMENT = 'added_payment'; // step 4 (complete)

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function updator()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function address()
    {
        return $this->evvAddress(); // shortcut to evvAddress
    }

    public function evvAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')
                    ->where('type', 'evv');
    }

    /**
     * Get the Client's billing address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function billingAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')
                    ->where('type', 'billing');
    }

    public function phoneNumber()
    {
        return $this->evvPhone();
    }

    public function evvPhone()
    {
        return $this->hasOne(PhoneNumber::class, 'user_id', 'id')
                    ->where('type', 'primary');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'client_caregivers')
                    ->with('user')
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

    public function caseManager()
    {
        return $this->belongsTo('App\OfficeUser', 'case_manager_id');
    }

    /**
     * A Client can have many ClientGoals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goals()
    {
        return $this->hasMany(ClientGoal::class);
    }

    /**
     * Determines if the given caregiver_id exists in the Client/Caregiver relationship.
     *
     * @param [type] $caregiver_id
     * @return boolean
     */
    public function hasCaregiver($caregiver_id)
    {
        return $this->caregivers()
            ->where('caregivers.id', $caregiver_id)
            ->exists();
    }

    public function defaultPayment()
    {
        return $this->morphTo('defaultPayment', 'default_payment_type', 'default_payment_id');
    }

    public function backupPayment()
    {
        return $this->morphTo('backupPayment', 'backup_payment_type', 'backup_payment_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function excludedCaregivers()
    {
        return $this->hasMany(ClientExcludedCaregiver::class);
    }

    public function carePlans()
    {
        return $this->hasMany(CarePlan::class)->with('activities');
    }

    public function preferences()
    {
        return $this->hasOne(ClientPreferences::class, 'id');
    }

    public function medications()
    {
        return $this->hasMany(ClientMedication::class);
    }

    public function referralServiceAgreement()
    {
        return $this->hasOne(ClientReferralServiceAgreement::class);
    }

    public function referralSource()
    {
        return $this->belongsTo('App\ReferralSource');
    }

    /**
     * Get the client's CareDetails relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function careDetails()
    {
        return $this->hasOne(CareDetails::class, 'client_id', 'id');
    }

    /**
     * Get the ClientNarrative relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function narrative()
    {
        return $this->hasMany(ClientNarrative::class)
            ->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payers()
    {
        return $this->hasMany(ClientPayer::class, 'client_id')
            ->orderBy('priority');
    }

    /** Current primary payer */
    public function primaryPayer()
    {
        $date = Carbon::now();

        return $this->hasOne(ClientPayer::class, 'client_id')
            ->where('effective_start', '<=', $date->toDateString())
            ->where('effective_end', '>=', $date->toDateString())
            ->orderBy('priority');
    }

    public function rates()
    {
        return $this->hasMany(ClientRate::class, 'client_id');
    }

    /**
     * Get the client authorizations relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function serviceAuthorizations()
    {
        return $this->hasMany(ClientAuthorization::class);
    }

    /**
     * Get the ClientContacts relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * Get the QuickbooksCustomer relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function quickbooksCustomer()
    {
        return $this->belongsTo(QuickbooksCustomer::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getLastServiceDateAttribute()
    {
        return optional($this->shifts()->orderBy('checked_in_time', 'desc')->first())->checked_in_time;
    }

    /**
     * Get the account setup URL.
     *
     * @return string
     */
    public function getSetupUrlAttribute()
    {
        return route('setup.clients', ['token' => $this->getEncryptedKey()]);
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    public function getAddress(): ?Address
    {
        return $this->evvAddress;
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->evvPhone;
    }

    /**
     * Get the client timezone (currently retrieved from the business record)
     *
     * @return string
     */
    public function getTimezone() : string
    {
        return Timezone::getTimezone($this->business_id);
    }

    /**
     * Get effective client payers
     *
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientPayer[]
     */
    public function getPayers(string $date = 'now'): Collection
    {
        $date = Carbon::parse($date, $this->getTimezone());

        return $query = $this->payers()->ordered()
            ->where('effective_start', '<=', $date->toDateString())
            ->where('effective_end', '>=', $date->toDateString())
            ->get();
    }

    /**
     * Get the default ClientRate for this client
     *
     * @param string $date
     * @return \App\Billing\ClientRate|null
     */
    public function getDefaultRate(string $date = 'now'): ?ClientRate
    {
        $date = Carbon::parse($date, $this->getTimezone());

        return $this->rates()
            ->whereNull('caregiver_id')
            ->whereNull('payer_id')
            ->whereNull('service_id')
            ->where('effective_start', '<=', $date->toDateString())
            ->where('effective_end', '>=', $date->toDateString())
            ->first();
    }

    /**
     * Set the client's preference data
     *
     * @param array $data
     * @return \App\ClientPreferences|false
     */
    public function setPreferences(array $data)
    {
        $preferences = $this->preferences()->firstOrNew([]);
        $preferences->fill($data);
        return $preferences->save() ? $preferences : false;
    }

    /**
     * @param $method
     * @return mixed|null|string
     */
    public function getPaymentType($method = null): PaymentMethodType
    {
        try {
            $payer = $this->primaryPayer;
            if ($payer && $method = $payer->getPaymentMethod()) {
                return $method->getPaymentType();
            }
        } catch (\Throwable $e) {
        }

        return PaymentMethodType::NONE();
    }

    /**
     * @param bool $backup
     * @return \App\Billing\Contracts\ChargeableInterface|null
     */
    public function getPaymentMethod($backup = false): ?ChargeableInterface
    {
        $method = ($backup) ? $this->backupPayment : $this->defaultPayment;
        return $method;
    }

    /**
     * Aggregate schedules for this client and return an array of events
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param bool $onlyStartTime Only include events matching the start time within the date range, otherwise include events that match start or end time
     *
     * @return array
     */
    public function getEvents($start, $end, $onlyStartTime = false)
    {
        $aggregator = new ScheduleAggregator();
        foreach ($this->schedules as $schedule) {
            $title = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            $aggregator->add($title, $schedule);
        }

        return $aggregator->onlyStartTime($onlyStartTime)->events($start, $end);
    }

    public function hasActiveShift()
    {
        return $this->shifts()->whereNull('checked_out_time')->exists();
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function clearFutureSchedules()
    {
        $this->schedules()
             ->where('starts_at', '>=', Carbon::today())
             ->doesntHave('shifts')
             ->delete();
    }

    /**
     * A client has many future schedules.
     *
     * @return void
     */
    public function futureSchedules()
    {
        return $this->schedules()
            ->where('starts_at', '>', Carbon::now());
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * @param \App\Billing\Contracts\ChargeableInterface $method
     * @param bool $backup
     * @return ChargeableInterface|false
     */
    public function setPaymentMethod(ChargeableInterface $method, $backup = false)
    {
        $method->user_id = $this->id;
        $relation = ($backup) ? 'backupPayment' : 'defaultPayment';
        $existing = $this->getPaymentMethod($backup);
        if ($existing && $existing->canBeMergedWith($method)) {
            if ($existing->mergeWith($method)) {
                return $existing;
            }
            return false;
        }

        if ($method->persistChargeable() && $this->$relation()->associate($method)->save()) {
            return $method;
        }
    }

    /**
     * Swap the client's primary and backup payment methods
     *
     * @throws \Exception
     */
    public function swapPaymentMethods()
    {
        $this->load(['defaultPayment', 'backupPayment']);
        $backup = $this->backupPayment;
        $default = $this->defaultPayment;
        if (! $backup || ! $default) {
            throw new \Exception('Client needs a backup and primary payment method for this method to work.');
        }
        $this->defaultPayment()->associate($backup)->save();
        $this->backupPayment()->associate($default)->save();
    }

    public function agreementStatusHistory()
    {
        return $this->hasMany(ClientAgreementStatusHistory::class);
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
                                 ->leftJoin('credit_cards', function ($q) {
                                     $q->on('credit_cards.id', '=', 'gateway_transactions.method_id')
                                       ->where('gateway_transactions.method_type', CreditCard::class);
                                 })
                                 ->whereHas('payment', function ($q) {
                                     $q->where('client_id', $this->id);
                                 })
                                 ->orWhere('bank_accounts.user_id', $this->id)
                                 ->orWhere('credit_cards.user_id', $this->id);
    }

    /**
     * Get all gateway transactions that relate to this client
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
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage()
    {
        if ($this->fee_override) {
            return (float) $this->fee_override;
        }

        if ($payer = $this->primaryPayer) {
            return $payer->getAllyPercentage();
        }

        // Default to CC fee
        return (float) config('ally.credit_card_fee');
    }

    /**
     * Remove missing ClientRates and update existing with the given
     * request values.
     *
     * @param array|null $rates
     * @return bool
     */
    public function syncRates(?iterable $rates) : bool
    {
        return ClientRate::sync($this, $rates);
    }

    /**
     * Remove missing ClientRates and update existing with the given
     * request values.
     *
     * @param array|null $payers
     * @return bool
     */
    public function syncPayers(?iterable $payers) : bool
    {
        return ClientPayer::sync($this, $payers);
    }

    /**
     * Get the client's service authorizations active on the
     * specified date.  Defaults to today.
     *
     * @param null|\Carbon\Carbon $date
     * @return \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientAuthorization[]
     */
    public function getActiveServiceAuths(?Carbon $date = null) : Collection
    {
        if (empty($date)) {
            $date = Carbon::now();
        }

        return $this->serviceAuthorizations()
            ->effectiveOn($date)
            ->get();
    }

    /**
     * Get the clients that belong to the specified chain.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int $chainId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForChain($query, $chainId)
    {
        return $query->whereHas('business', function ($q) use ($chainId) {
            $q->where('businesses.chain_id', $chainId);
        });
    }

    /**
     * Get the Client's billing address, or their EVV
     * address, or ANY address.
     *
     * @return Address|null
     */
    public function getBillingAddress() : ?Address
    {
        if (filled($this->billingAddress)) {
            return $this->billingAddress;
        }

        if (filled($this->evvAddress)) {
            return $this->evvAddress;
        }

        return $this->addresses->first();
    }
}
