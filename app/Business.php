<?php

namespace App;

use App\Billing\BillingCalculator;
use App\Billing\Deposit;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\PaymentMethodType;
use App\Contracts\BelongsToBusinessesInterface;
use App\Contracts\BelongsToChainsInterface;
use App\Billing\Contracts\ChargeableInterface;
use App\Contracts\ContactableInterface;
use App\Contracts\HasPaymentHold;
use App\Billing\Contracts\ReconcilableInterface;
use App\Contracts\HasTimezone;
use App\Exceptions\ExistingBankAccountException;
use App\Traits\BelongsToBusinesses;
use App\Traits\BelongsToOneChain;
use App\BusinessCommunications;
use App\Traits\ScrubsForSeeding;
use Crypt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Business
 *
 * @property int $id
 * @property string $name
 * @property string $short_name
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
 * @property int $calendar_remember_filters
 * @property int $auto_confirm
 * @property int $ask_on_confirm
 * @property string|null $contact_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property int $allows_manual_shifts
 * @property int $location_exceptions
 * @property int $timesheet_exceptions
 * @property int $require_signatures
 * @property int $co_mileage
 * @property int $co_injuries
 * @property int $co_comments
 * @property int $co_expenses
 * @property int $co_issues
 * @property int $co_signature
 * @property string $calendar_next_day_threshold
 * @property mixed|null $ein
 * @property string|null $medicaid_id
 * @property string|null $medicaid_npi_number
 * @property string|null $medicaid_npi_taxonomy
 * @property string|null $medicaid_license_number
 * @property string|null $outgoing_sms_number
 * @property string $shift_rounding_method
 * @property string|null $pay_cycle
 * @property string|null $last_day_of_cycle
 * @property string|null $last_day_of_first_period
 * @property string|null $mileage_reimbursement_rate
 * @property array $unpaired_pay_rates
 * @property string|null $overtime_hours_day
 * @property string|null $overtime_hours_week
 * @property string|null $overtime_consecutive_days
 * @property string|null $dbl_overtime_hours_day
 * @property string|null $dbl_overtime_consecutive_days
 * @property string|null $overtime_method
 * @property int $allow_client_confirmations
 * @property int $auto_confirm_modified
 * @property int $shift_confirmation_email
 * @property int $sce_shifts_in_progress
 * @property int $charge_diff_email
 * @property int $auto_append_hours
 * @property int $auto_confirm_unmodified_shifts
 * @property int $auto_confirm_verified_shifts
 * @property string $rate_structure
 * @property int $include_ally_fee
 * @property int $use_rate_codes
 * @property int|null $chain_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\Payments\Methods\BankAccount|null $bankAccount
 * @property-read \App\BusinessChain|null $businessChain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \App\BusinessChain|null $chain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\GatewayTransaction[] $chargedTransactions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clientsUsingProviderPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SystemException[] $exceptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\NoteTemplate[] $noteTemplates
 * @property-read \App\Billing\Payments\Methods\BankAccount|null $paymentAccount
 * @property-read \App\PaymentHold $paymentHold
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Prospect[] $prospects
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\RateCode[] $rateCodes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SmsThread[] $smsThreads
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Task[] $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Timesheet[] $timesheets
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAllowClientConfirmations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAllowsManualShifts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAskOnConfirm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAutoAppendHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAutoConfirm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAutoConfirmModified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAutoConfirmUnmodifiedShifts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAutoConfirmVerifiedShifts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCalendarCaregiverFilter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCalendarDefaultView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCalendarNextDayThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCalendarRememberFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereChainId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereChargeDiffEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCoComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCoExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCoInjuries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCoIssues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCoMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCoSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereDblOvertimeConsecutiveDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereDblOvertimeHoursDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereDefaultCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereEin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereIncludeAllyFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereLastDayOfCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereLastDayOfFirstPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereLocationExceptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMedicaidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMedicaidNpiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMedicaidNpiTaxonomy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMileageRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMileageReimbursementRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMultiLocationRegistry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereOutgoingSmsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereOvertimeConsecutiveDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereOvertimeHoursDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereOvertimeHoursWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereOvertimeMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePayCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePaymentAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereRateStructure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereRequireSignatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereSceShiftsInProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereScheduling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereShiftConfirmationEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereShiftRoundingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereTimesheetExceptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereUnpairedPayRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereUseRateCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereZip($value)
 * @mixin \Eloquent
 * @property int $enable_client_onboarding
 * @property float $ot_multiplier
 * @property string|null $ot_behavior
 * @property float $hol_multiplier
 * @property string|null $hol_behavior
 * @property string|null $hha_username
 * @property mixed|null $hha_password
 * @property string|null $tellus_username
 * @property mixed|null $tellus_password
 * @property int $co_caregiver_signature
 * @property int $require_caregiver_signatures
 * @property string $logo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeUser[] $activeUsers
 * @property-read int|null $active_users_count
 * @property-read int|null $activities_count
 * @property-read int|null $audits_count
 * @property-read int|null $care_plans_count
 * @property-read int|null $caregivers_count
 * @property-read int|null $charged_transactions_count
 * @property-read int|null $clients_count
 * @property-read int|null $clients_using_provider_payment_count
 * @property-read \App\BusinessCommunications $communicationSettings
 * @property-read int|null $deposits_count
 * @property-read string $city_state_zip
 * @property-read mixed $has_open_shifts
 * @property-read string|null $street_address
 * @property-read int|null $note_templates_count
 * @property-read int|null $notes_count
 * @property-read int|null $payments_count
 * @property-read int|null $prospects_count
 * @property-read int|null $questions_count
 * @property-read \App\QuickbooksConnection $quickbooksConnection
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\QuickbooksCustomer[] $quickbooksCustomers
 * @property-read int|null $quickbooks_customers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\QuickbooksService[] $quickbooksServices
 * @property-read int|null $quickbooks_services_count
 * @property-read int|null $rate_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SalesPerson[] $salesPeople
 * @property-read int|null $sales_people_count
 * @property-read int|null $schedules_count
 * @property-read int|null $shifts_count
 * @property-read int|null $sms_threads_count
 * @property-read int|null $tasks_count
 * @property-read int|null $timesheets_count
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business query()
 */
class Business extends AuditableModel implements ChargeableInterface, ReconcilableInterface, HasPaymentHold,
    BelongsToBusinessesInterface, BelongsToChainsInterface, ContactableInterface, HasTimezone
{
    use BelongsToBusinesses, BelongsToOneChain;
    use \App\Traits\HasPaymentHold;
    use \App\Traits\HasAllyFeeTrait;

    protected $table = 'businesses';
    protected $guarded = ['id'];

    protected $casts = [
        'unpaired_pay_rates' => 'json',
    ];

    protected $appends = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // populate settings defaults here because the change() method
            // on migration columns is not supported due to the business
            // table having an enum column.
            $model->shift_confirmation_email = false;
            $model->allow_client_confirmations = false;
        });
    }

    ///////////////////////////////////////////
    /// Business type constants
    ///////////////////////////////////////////

    const TYPE_AGENCY = 'Agency';
    const TYPE_DRA = 'DRA';
    const TYPE_FRANCHISOR = 'Franchisor';
    const TYPE_REGISTRY = 'Registry';

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

    public function chain()
    {
        return $this->belongsTo(BusinessChain::class, 'chain_id');
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
        return $this->belongsToMany(Caregiver::class, 'business_caregivers');
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentHold()
    {
        return $this->hasOne(PaymentHold::class, 'business_id');
    }

    public function prospects()
    {
        return $this->hasMany(Prospect::class, 'business_id');
    }

    public function rateCodes()
    {
        return $this->hasMany(RateCode::class, 'business_id');
    }

    public function users()
    {
        return $this->belongsToMany(OfficeUser::class, 'business_office_users');
    }

    /**
     * Get the office users relation (active only).
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeUsers()
    {
        return $this->users()
            ->whereHas('user', function($q) { $q->where('active', 1); });
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

    public function noteTemplates()
    {
        return $this->hasMany(NoteTemplate::class);
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

    /**
     * A business can have many Tasks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the businesses SMS threads relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function smsThreads()
    {
        return $this->hasMany(SmsThread::class);
    }

    public function salesPeople()
    {
        return $this->hasMany(SalesPerson::class);
    }

    /**
     * Get the QuickbooksConnection relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function quickbooksConnection()
    {
        return $this->hasOne(QuickbooksConnection::class);
    }

    /**
     * Get the QuickbooksCustomer relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function quickbooksCustomers()
    {
        return $this->hasMany(QuickbooksCustomer::class);
    }

    /**
     * Get the QuickbooksService relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function quickbooksServices()
    {
        return $this->hasMany(QuickbooksService::class);
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
     * @return \App\Billing\Payments\Methods\BankAccount|null
     */
    public function getBankAccount($relation)
    {
        return $this->$relation;
    }

    /**
     * @param string $relation Ex: paymentAccount
     * @param \App\Billing\Payments\Methods\BankAccount $account
     * @return \App\Billing\Payments\Methods\BankAccount|bool
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
     * @return \App\Billing\GatewayTransaction|false
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
     * @param \App\Billing\GatewayTransaction $transaction
     * @param $amount
     * @return \App\Billing\GatewayTransaction|false
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
     * @return \App\Billing\GatewayTransaction[]|\Illuminate\Support\Collection
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
     * @deprecated
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
        return BillingCalculator::getBankAccountRate();
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
     * @return array
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

    /**
     * Get a simple list of office users names and ids.
     *
     * @param boolean $lastFirst
     * @param boolean $activeOnly
     * @return void
     */
    public function officeUserList($lastFirst = true, $activeOnly = false)
    {
        $sort = $lastFirst ? 'nameLastFirst' : 'name';

        $query = $activeOnly ? $this->activeUsers() : $this->users();

        return $query->get()
            ->sortBy($sort, SORT_NATURAL|SORT_FLAG_CASE)->map(function ($item) use($lastFirst) {
                return [
                    'id' => $item->id,
                    'name' => $lastFirst ? $item->nameLastFirst : $item->name,
                ];
        })->values();
    }

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return [$this->id];
    }

    /**
     * Get a list of OfficeUser's notifiable User objects
     * that should be sent notifications.
     *
     * @return array|Collection
     */
    public function notifiableUsers()
    {
        return $this->users()->with(['user', 'user.notificationPreferences'])
            ->whereHas('user', function ($q) {
                $q->where('active', true);
            })
            ->get()
            ->pluck('user');
    }

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwnerModel()
    {
        return $this;
    }

    function name(): string
    {
        return $this->name;
    }

    function getAddress(): ?Address
    {
        if (!$this->address1) {
            return null;
        }

        return new Address([
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => 'US',
        ]);
    }

    /**
     * Get the business's EIN number.
     *
     * @return null|string
     */
    public function getEinNumber() : ?string
    {
        return $this->ein;
    }

    /**
     * Get the business's NPI Number.
     *
     * @return null|string
     */
    public function getNpiNumber(): ?string
    {
        return $this->medicaid_npi_number;
    }

    /**
     * Get the business's License Number.
     *
     * @return null|string
     */
    public function getMedicaidLicenseNumber(): ?string
    {
        return $this->medicaid_license_number;
    }

    /**
     * @return string|null
     */
    public function getStreetAddressAttribute()
    {
        $fullAddress = $this->address1;

        if (!empty($this->address2)) {
            $fullAddress .= ' ' . $this->address2;
        }

        return $fullAddress;
    }

    /**
     * @return string
     */
    public function getCityStateZipAttribute(){
        return $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;
    }

    function getPhoneNumber(): ?PhoneNumber
    {
        try {
            $phone = new PhoneNumber();
            $phone->input($this->phone1);
            return $phone;
        }
        catch (\Exception $e) {}
        return null;
    }

    function getBillingName(): string
    {
        return $this->name();
    }

    function getBillingAddress(): ?Address
    {
        return $this->getAddress();
    }

    function getBillingPhone(): ?PhoneNumber
    {
        return $this->getPhoneNumber();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return 'businesses:' . $this->id;
    }

    public function getPaymentType(): PaymentMethodType
    {
        return PaymentMethodType::ACH_P();
    }

    /**
     * Return a display value of the payment method.  Ex.  VISA *0925
     *
     * @return string
     */
    public function getDisplayValue(): string
    {
        if (empty($this->paymentAccount)) {
            return 'ACH-P Missing';
        }

        return 'ACH-P *' . $this->paymentAccount->last_four;
    }

    /**
     * Setter for hha_password field.
     *
     * @param $value
     */
    public function setHhaPassword($value) : void
    {
        $this->attributes['hha_password'] = $value ? Crypt::encrypt($value) : null;
    }

    /**
     * Getter for hha_password field.
     *
     * @return string
     */
    public function getHhaPassword() : string
    {
        return empty($this->attributes['hha_password']) ? '' : Crypt::decrypt($this->attributes['hha_password']);
    }

    /**
     * Setter for tellus_password field.
     *
     * @param $value
     */
    public function setTellusPassword($value) : void
    {
        $this->attributes['tellus_password'] = $value ? Crypt::encrypt($value) : null;
    }

    /**
     * Getter for tellus_password field.
     *
     * @return string
     */
    public function getTellusPassword() : string
    {
        return empty($this->attributes['tellus_password']) ? '' : Crypt::decrypt($this->attributes['tellus_password']);
    }

    /**
     * Attach a Caregiver to the business and also the parent chain.
     *
     * @param \App\Caregiver $caregiver
     * @return bool
     */
    public function assignCaregiver(Caregiver $caregiver) : bool
    {
        // Assign to the parent chain first.
        if (! $this->businessChain->caregivers()->where('caregiver_id', $caregiver->id)->exists()) {
            if (! $this->businessChain->caregivers()->save($caregiver)) {
                return false;
            }
        }

        // Assign to this business location.
        if (! $this->caregivers()->where('caregiver_id', $caregiver->id)->exists()) {
            if (! $this->caregivers()->save($caregiver)) {
                return false;
            }
        }

        return true;
    }

    public function communicationSettings(){
        return $this->hasOne(BusinessCommunications::class);
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->whereIn('id', $businessIds);
    }

    /**
     * Get the extra data that should be printed on invoices.
     *
     * @return array
     */
    function getExtraInvoiceData(): array
    {
        if (filled($this->medicaid_license_number)) {
            return ['License #:' . $this->medicaid_license_number];
        }

        return [];
    }

    /**
     * Get the model's Timezone.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone ? $this->timezone : config('ally.local_timezone');
    }

    /**
     * Get the business logo filename.
     *
     * @return string
     */
    public function getLogoAttribute() : string
    {
        if (filled($this->attributes['logo'])) {
            return \Storage::disk('public')->url($this->attributes['logo']);
        } else {
            return config('ally.logo.invoice');
        }
    }

    /**
     * Set (and store) the business logo from uploaded image data.
     *
     * @param $value
     */
    public function setLogoAttribute($value)
    {
        if (empty($value) || $value == config('ally.logo.invoice')) {
            $this->attributes['logo'] = null;
            return;
        }

        if (Str::startsWith($value, config('app.url'))) {
            return;
        }

        $base64Data = str_replace('data:image/png;base64,', '', $value);
        $base64Data = str_replace(' ', '+', $base64Data);

        $filename = 'logos/' . $this->id . '_' . Str::slug($this->name) . '.png';

        if (\Storage::disk('public')->put($filename, base64_decode($base64Data))) {
            $this->attributes['logo'] = $filename;
        }
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

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
            'address1' => $faker->streetAddress,
            'address2' => null,
            'contact_name' => $faker->name,
            'contact_email' => $faker->companyEmail,
            'contact_phone' => $faker->simple_phone,
            'outgoing_sms_number' => $faker->simple_phone,
            'ein' => $faker->randomNumber(9, true),
            'medicaid_id' => $faker->randomNumber(9, true),
            'medicaid_npi_number' => $faker->randomNumber(9, true) . "1",
            'medicaid_npi_taxonomy' => $faker->randomNumber(9, true),
            'medicaid_license_number' => $faker->randomNumber(9, true),
        ];
    }
}
