<?php

namespace App;

use App\Confirmations\Confirmation;
use App\Contracts\CanBeConfirmedInterface;
use App\Contracts\ChargeableInterface;
use App\Contracts\HasAllyFeeInterface;
use App\Contracts\HasPaymentHold;
use App\Contracts\ReconcilableInterface;
use App\Contracts\UserRole;
use App\Notifications\ClientConfirmation;
use App\Scheduling\ScheduleAggregator;
use App\Traits\BelongsToOneBusiness;
use App\Traits\HasAllyFeeTrait;
use App\Traits\HasDefaultRates;
use App\Traits\HasPaymentHold as HasPaymentHoldTrait;
use App\Traits\HasSSNAttribute;
use App\Traits\IsUserRole;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Packages\MetaData\HasOwnMetaData;

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
 * @property string|null $onboard_status
 * @property string|null $deleted_at
 * @property float|null $fee_override
 * @property float $max_weekly_hours
 * @property \Carbon\Carbon|null $inquiry_date
 * @property \Carbon\Carbon|null $service_start_date
 * @property string|null $referral
 * @property string|null $diagnosis
 * @property int|null $ambulatory
 * @property string|null $poa_first_name
 * @property string|null $poa_last_name
 * @property string|null $poa_phone
 * @property string|null $poa_relationship
 * @property string|null $import_identifier
 * @property string|null $dr_first_name
 * @property string|null $dr_last_name
 * @property string|null $dr_phone
 * @property string|null $dr_fax
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $backupPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \App\Business $business
 * @property-read \App\CareDetails $careDetails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \App\RateCode|null $defaultFixedRate
 * @property-read \App\RateCode|null $defaultHourlyRate
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $defaultPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read \App\Address $evvAddress
 * @property-read \App\PhoneNumber $evvPhone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientExcludedCaregiver[] $excludedCaregivers
 * @property-read mixed $active
 * @property-read mixed $ally_percentage
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OnboardStatusHistory[] $onboardStatusHistory
 * @property-read \App\PaymentHold $paymentHold
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \App\ClientPreferences $preferences
 * @property-read \App\ClientReferralServiceAgreement $referralServiceAgreement
 * @property-read \App\ReferralSource|null $referralSource
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\User $user
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDrFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDrFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDrLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDrPhone($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client wherePoaFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client wherePoaLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client wherePoaPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client wherePoaRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereReceiveSummaryEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereReferral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereReferralSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereServiceStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereSsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client withMeta()
 * @mixin \Eloquent
 */
class Client extends AuditableModel implements UserRole, CanBeConfirmedInterface, ReconcilableInterface, HasPaymentHold, HasAllyFeeInterface
{
    use IsUserRole, BelongsToOneBusiness, Notifiable;
    use HasSSNAttribute, HasPaymentHoldTrait, HasAllyFeeTrait, HasOwnMetaData, HasDefaultRates;

    protected $table = 'clients';
    public $timestamps = false;
    public $hidden = ['ssn'];
    public $dates = ['service_start_date', 'inquiry_date'];
    public $appends = ['payment_type', 'ally_percentage'];
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
        'poa_first_name',
        'poa_last_name',
        'poa_phone',
        'poa_relationship',
        'import_identifier',
        'dr_first_name',
        'dr_last_name',
        'dr_phone',
        'dr_fax',
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
        'fixed_rate_id'
    ];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

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
        return $this->morphTo('default_payment');
    }

    public function backupPayment()
    {
        return $this->morphTo('backup_payment', 'backup_payment_type', 'backup_payment_id');
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

    public function referralSource() {
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

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getPaymentTypeAttribute()
    {
        return $this->getPaymentType();
    }

    public function getAllyPercentageAttribute()
    {
        return $this->getAllyPercentage();
    }


    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Set the client's preference data
     *
     * @param array $data
     * @return \App\ClientPreferences|false
     */
    public function setPreferences(array $data) {
        $preferences = $this->preferences()->firstOrNew([]);
        $preferences->fill($data);
        return $preferences->save() ? $preferences : false;
    }

    /**
     * @param $method
     * @return mixed|null|string
     */
    public function getPaymentType($method = null)
    {
        if (!$method) {
            $method = $this->getPaymentMethod();
        }

        if ($method instanceof Business) {
            return 'ACH-P';
        }

        if ($method instanceof CreditCard) {
            if ($method->type == 'amex') {
                return 'AMEX';
            }
            return 'CC';
        }

        if ($method instanceof BankAccount) {
            return 'ACH';
        }

        return 'NONE';
    }

    /**
     * @param bool $backup
     * @return \App\Contracts\ChargeableInterface
     */
    public function getPaymentMethod($backup = false)
    {
        $method = ($backup) ? $this->backupPayment : $this->defaultPayment;
        return $method;
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
     * Set the generated fake email address for a client that does not have an email address.
     *
     * @return $this
     */
    public function setAutoEmail()
    {
        $this->email = $this->getAutoEmail();
        return $this;
    }

    /**
     * Aggregate schedules for this client and return an array of events
     *
     * @param string|\DateTime $start
     * @param string|\DateTime $end
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
     * @param \App\Contracts\ChargeableInterface $method
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

    public function sendConfirmationEmail()
    {
        $confirmation = new Confirmation($this);
        $confirmation->touchTimestamp();

        $status = 'emailed_reconfirmation';
        $this->update(['onboard_status' => $status]);
        $history = new OnboardStatusHistory(compact('status'));
        $this->onboardStatusHistory()->save($history);

        $this->notify(new ClientConfirmation($this, $this->business));
    }

    public function onboardStatusHistory()
    {
        return $this->hasMany(OnboardStatusHistory::class);
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
                                 ->leftJoin('credit_cards', function($q) {
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
     * @return \App\GatewayTransaction[]|\Illuminate\Support\Collection
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

        if ($this->defaultPayment) {
            return $this->defaultPayment->getAllyPercentage();
        }

        if ($this->backupPayment) {
            return $this->backupPayment->getAllyPercentage();
        }

        // Default to CC fee
        return (float) config('ally.credit_card_fee');
    }

}
