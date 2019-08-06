<?php

namespace App;

use App\Billing\CaregiverInvoice;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;
use App\Billing\Invoiceable\InvoiceableModel;
use App\Billing\Invoiceable\ShiftExpense;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Billing\Queries\InvoiceableQuery;
use App\Billing\Service;
use App\Businesses\Timezone;
use App\Contracts\BelongsToBusinessesInterface;
use App\Contracts\HasAllyFeeInterface;
use App\Events\ShiftCreated;
use App\Events\ShiftModified;
use App\Payments\MileageExpenseCalculator;
use App\Shifts\Contracts\ShiftDataInterface;
use App\Shifts\CostCalculator;
use App\Shifts\Data\ClockData;
use App\Shifts\DurationCalculator;
use App\Shifts\ShiftFactory;
use App\Shifts\ShiftFlagManager;
use App\Shifts\ShiftStatusManager;
use App\Traits\BelongsToOneBusiness;
use App\Traits\HasAllyFeeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Events\ShiftDeleted;
use Illuminate\Support\Collection;
use App\Data\ScheduledRates;

/**
 * App\Shift
 *
 * @property int $id
 * @property int|null $caregiver_id
 * @property int|null $client_id
 * @property int|null $business_id
 * @property int|null $schedule_id
 * @property string $checked_in_method
 * @property \Carbon\Carbon|null $checked_in_time
 * @property float|null $checked_in_latitude
 * @property float|null $checked_in_longitude
 * @property int|null $checked_in_distance The distance in meters from the client evv address.
 * @property string|null $checked_in_agent
 * @property string|null $checked_in_ip
 * @property int $checked_in_verified
 * @property string|null $checked_in_number evv phone number
 * @property string $checked_out_method
 * @property \Carbon\Carbon|null $checked_out_time
 * @property float|null $checked_out_latitude
 * @property float|null $checked_out_longitude
 * @property int|null $checked_out_distance The distance in meters from the client evv address.
 * @property string|null $checked_out_agent
 * @property string|null $checked_out_ip
 * @property int $checked_out_verified
 * @property string|null $checked_out_number evv phone number
 * @property string|null $caregiver_comments
 * @property float|null $hours
 * @property string|null $hours_type
 * @property float $mileage
 * @property float $other_expenses
 * @property int $verified
 * @property int $fixed_rates
 * @property float $caregiver_rate
 * @property float $provider_fee
 * @property string|null $status
 * @property int|null $payment_id
 * @property string|null $other_expenses_desc
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $import_id
 * @property int|null $timesheet_id
 * @property int|null $address_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Address|null $address
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client|null $client
 * @property-read \App\ShiftCostHistory $costHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SystemNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SystemException[] $exceptions
 * @property-read mixed $ally_pct
 * @property-read mixed $charged_at
 * @property-read mixed $confirmed_at
 * @property-read mixed $duration
 * @property-read mixed $read_only
 * @property-read mixed $timezone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClientGoal[] $goals
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftIssue[] $issues
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftActivity[] $otherActivities
 * @property-read \App\Billing\Payment|null $payment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Invoiceable\ShiftService[] $services
 * @property-read \App\Schedule|null $schedule
 * @property-read \App\Signature $signature
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftStatusHistory[] $statusHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift betweenDates($start, $end)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift forBusiness($business)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift forCaregiver($caregiver)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift forClient($client)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingBusinessDeposit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingCaregiverDeposit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingCharge()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereConfirmed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereFixedRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereMobileVerified()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereOtherExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereOtherExpensesDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift wherePending()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereReadOnly()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereTelephonyVerified()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereTimesheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereUnconfirmed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereVerified($value)
 * @mixin \Eloquent
 * @property int $client_confirmed
 * @property int|null $duplicated_by
 * @property-read \App\Shift|null $duplicatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $duplicates
 * @property-read array $flags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftFlag[] $shiftFlags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereClientConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereDuplicatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereFlagsIn($flags)
 * @property int $quickbooks_service_id
 */
class Shift extends InvoiceableModel implements HasAllyFeeInterface, BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;
    use HasAllyFeeTrait;

    protected $appends = ['duration', 'readOnly', 'flags'];
    protected $dates = ['checked_in_time', 'checked_out_time', 'signature'];
    protected $guarded = ['id'];
    protected $orderedColumn = ['checked_in_time'];
    protected $with = ['shiftFlags'];

    ///////////////////////////////////////////
    /// Events
    ///////////////////////////////////////////

    protected $dispatchesEvents = [
        'created' => ShiftCreated::class,
        'updated' => ShiftModified::class,
        // 'deleted' => ShiftDeleted::class,
    ];

    public static function boot()
    {
        parent::boot();
        self::recalculateDurationOnChange();
        self::deleted(function(Shift $shift) {
            event(new ShiftDeleted($shift));
        });
    }

    public static function recalculateDurationOnChange()
    {
        self::saving(function (Shift $shift) {
            if ($shift->checked_out_time &&
                ($shift->isDirty('checked_out_time') || $shift->isDirty('checked_in_time'))
            ) {
                $shift->hours = $shift->duration(true);
            }
        });
    }

    ///////////////////////////////////////
    /// Shift Statuses
    ///////////////////////////////////////

    const CLOCKED_IN = 'CLOCKED_IN';
    const CLOCKED_OUT = 'CLOCKED_OUT'; // not currently used
    const WAITING_FOR_CONFIRMATION = 'WAITING_FOR_CONFIRMATION';  // Unconfirmed shift that needs to be approved
    const WAITING_FOR_AUTHORIZATION = 'WAITING_FOR_AUTHORIZATION';  // Confirmed shift that needs to be authorized for payment
    // Read-only statuses from here down (see isReadOnly())
    const WAITING_FOR_INVOICE = 'WAITING_FOR_INVOICE';  // Authorized shift that is waiting for invoicing
    const WAITING_FOR_CHARGE = 'WAITING_FOR_CHARGE';  // Invoiced shift that is waiting for payment
    const WAITING_FOR_PAYOUT = 'WAITING_FOR_PAYOUT';  // Charged shift that is waiting for payout (settlement)
    const PAID_BUSINESS_ONLY = 'PAID_BUSINESS_ONLY'; // Shift that failed payment to the caregiver, but paid successfully to the business
    const PAID_CAREGIVER_ONLY = 'PAID_CAREGIVER_ONLY'; // Shift that failed payment to the business, but paid successfully to the caregiver
    const PAID_BUSINESS_ONLY_NOT_CHARGED = 'PAID_BUSINESS_ONLY_NOT_CHARGED'; // Shift that failed payment to the caregiver, paid successfully to the business, but still requires payment from the client
    const PAID_CAREGIVER_ONLY_NOT_CHARGED = 'PAID_CAREGIVER_ONLY_NOT_CHARGED'; // Shift that failed payment to the business, paid successfully to the caregiver, but still requires payment from the client
    const PAID_NOT_CHARGED = 'PAID_NOT_CHARGED';  // Shift that was paid out to both business & caregiver but still requires payment from the client
    const PAID = 'PAID';  // Shift that has been successfully charged and paid out (FINAL)

    ////////////////////////////////////
    //// Shift Methods
    ////////////////////////////////////

    const METHOD_CONVERTED = 'Converted';  //  The shift was converted from a schedule
    const METHOD_GEOLOCATION = 'Geolocation';  //  The shift was clocked in/out from the mobile app using geolocation
    const METHOD_OFFICE = 'Office';  //  The shift was manually created or clocked out from the office user interface
    const METHOD_TELEPHONY = 'Telephony';  //  The shift was clocked in/out from the telephony system
    const METHOD_TIMESHEET = 'Timesheet';  //  The shift was created from a manual timesheet submitted by the caregiver
    const METHOD_IMPORTED = 'Imported';  //  The shift was imported manually or through a third party interface
    const METHOD_UNKNOWN = 'Unknown';  //  The check in/out method is unknown, most likely from before we implemented this logic

    ////////////////////////////////////
    //// Shift Hour Types
    ////////////////////////////////////

    const HOURS_DEFAULT = 'default';
    const HOURS_OVERTIME = 'overtime';
    const HOURS_HOLIDAY = 'holiday';

    //////////////////////////////////////
    /// Relationship Methods
    //////////////////////////////////////

    /**
     * Get the shift's address relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function deposits()
    {
        return $this->belongsToMany(Deposit::class, 'deposit_shifts');
    }

    public function client()
    {
        return $this->belongsTo(Client::class)
            ->withTrashed();
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class)
            ->withTrashed();
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'shift_activities')
            ->orderBy('code')
            ->withPivot(['completed', 'other']);
    }

    public function otherActivities()
    {
        return $this->hasMany(ShiftActivity::class)->select(['id', 'other', 'completed']);
    }

    public function allActivities()
    {
        return $this->activities->merge($this->otherActivities);
    }

    public function issues()
    {
        return $this->hasMany(ShiftIssue::class);
    }

    public function systemNotifications()
    {
        return $this->morphMany(SystemNotification::class, 'reference');
    }

    public function costHistory()
    {
        return $this->hasOne(ShiftCostHistory::class, 'id');
    }

    public function signature()
    {
        return $this->morphOne(Signature::class, 'signable');
    }

    public function statusHistory()
    {
        return $this->hasMany(ShiftStatusHistory::class);
    }

    /**
     * A Shift can have many ClientGoals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function goals()
    {
        return $this->belongsToMany(ClientGoal::class, 'shift_goals')
            ->withPivot('comments');
    }

    /**
     * A Shift can have many Questions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'shift_questions')
            ->withPivot('answer');
    }

    /**
     * Get the ShiftFlags relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shiftFlags()
    {
        return $this->hasMany(ShiftFlag::class);
    }

    public function duplicates()
    {
        return $this->hasMany(Shift::class, 'duplicated_by');
    }

    public function duplicatedBy()
    {
        return $this->belongsTo(Shift::class, 'duplicated_by');
    }

    public function services()
    {
        return $this->hasMany(ShiftService::class);
    }

    public function expenses()
    {
        return $this->hasMany(ShiftExpense::class);
    }

    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }


    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getTimezoneAttribute()
    {
        return $this->getTimezone();
    }

    public function getDurationAttribute()
    {
        return $this->duration();
    }

    public function getReadOnlyAttribute()
    {
        return $this->isReadOnly();
    }

    public function getConfirmedAtAttribute()
    {
        $date = $this->statusHistory
            ->where('new_status', 'WAITING_FOR_AUTHORIZATION')
            ->pluck('created_at')
            ->first();

        return optional($date)->toDateTimeString();
    }

    public function getChargedAtAttribute()
    {
        $date = $this->statusHistory
            ->where('new_status', 'WAITING_FOR_PAYOUT')
            ->pluck('created_at')
            ->first();

        return optional($date)->toDateTimeString();
    }

    public function getAllyPctAttribute()
    {
        return $this->getAllyPercentage();
    }

    /**
     * Skip updates that only modify the seconds.
     * @param $value
     */
    public function setCheckedInTimeAttribute($value)
    {
        if (!$this->checked_in_time || $this->checked_in_time->copy()->second(0) != $value) {
            $this->attributes['checked_in_time'] = $value;
        }
    }

    /**
     * Skip updates that only modify the seconds.
     * @param $value
     */
    public function setCheckedOutTimeAttribute($value)
    {
        if (!$this->checked_out_time || $this->checked_out_time->copy()->second(0) != $value) {
            $this->attributes['checked_out_time'] = $value;
        }
    }

    /**
     * Get the Shift's flags in array form.
     *
     * @return array
     */
    public function getFlagsAttribute()
    {
        return $this->shiftFlags->pluck('flag')->unique()->values()->toArray();
    }

    //////////////////////////////////////
    /// Instance Methods
    //////////////////////////////////////

    /**
     * Get the abbreviation code for the hours type of the current shift.
     *
     * @return string
     */
    public function getPaycode() : string
    {
        switch ($this->hours_type) {
            case self::HOURS_HOLIDAY:
                return 'HOL';
            case self::HOURS_OVERTIME:
                return 'OVT';
            case self::HOURS_DEFAULT:
            default:
                return 'REG';
        }
    }

    /**
     * Add data to the shift from a shift data class
     *
     * @param \App\Shifts\Contracts\ShiftDataInterface ...$dataObjects
     */
    public function addData(ShiftDataInterface ...$dataObjects): void
    {
        foreach($dataObjects as $data) {
            $this->fill($data->toArray());
        }
    }

    /**
     * Get an instance of the shift's flag manager class.
     *
     * @return App\Shifts\ShiftFlagManager
     */
    public function flagManager() : ShiftFlagManager
    {
        return new ShiftFlagManager($this);
    }

    /**
     * @param iterable $services
     */
    public function syncServices(iterable $services)
    {
        $savedIds = [];
        foreach($services as $data) {
            $service = null;
            if (isset($data['id'])) {
                $service = $this->services()->find($data['id']);
            }
            if (!$service) {
                $service = new ShiftService();
            }

            // Resolve default rates
            if ($data['client_rate'] === null) {
                $rates = ShiftFactory::resolveRates(
                    new ClockData($this->checked_in_method, $this->checked_in_time->toDateTimeString()),
                    new ScheduledRates(null, null, false, $data['hours_type']),
                    $this->client_id,
                    $this->caregiver_id,
                    $data['service_id'],
                    $data['payer_id']
                );
                $data['client_rate'] = $rates->clientRate();
                $data['caregiver_rate'] = $rates->caregiverRate();
            }

            $service->fill($data);
            $this->services()->save($service);
            $savedIds[] = $service->id;
        }
        // Delete Others
        $this->services()->whereNotIn('id', $savedIds)->delete();
        if (count($savedIds)) {
            // Enforce either a single service shift or a service breakout shift, not both
            $this->update(['service_id' => null]);
        }
    }

    /**
     * Return the number of hours worked, calculate if not persisted
     *
     * @param bool $forceRecalculation
     * @return float
     */
    public function duration($forceRecalculation = false)
    {
        if (!$forceRecalculation && $this->hours) {
            return $this->hours;
        }

        return app(DurationCalculator::class)->getDuration($this);
    }

    /**
     * Get the scheduled start time of the shift.
     *
     * @return Carbon|null
     */
    public function scheduledStartTime()
    {
        if (filled($this->schedule)) {
            return $this->schedule->getStartDateTime();
        }

        return $this->checked_in_time;
    }

    /**
     * Get the scheduled end time of the shift
     *
     * @return Carbon
     */
    public function scheduledEndTime()
    {
        if (filled($this->schedule)) {
            return $this->schedule->getEndDateTime();
        }

        if (filled($this->checked_out_time)) {
            return $this->checked_out_time;
        }

        // Return now if no schedule and still clocked in.
        return Carbon::now();
    }

    /**
     * Return the number of hours remaining in the shift (as scheduled)
     *
     * @return float|int
     */
    public function remaining()
    {
        if ($this->checked_out_time) return 0;
        $end = $this->scheduledEndTime();
        $now = Carbon::now();

        if ($now >= $end) return 0;
        return round($now->diffInMinutes($end) / 60, 2);
    }

    /**
     * Return an instance of the CostCalculator for this shift
     *
     * @return \App\Shifts\CostCalculator
     */
    public function costs()
    {
        return new CostCalculator($this);
    }

    /**
     * Return an instance of the ShiftStatusManager for this shift
     *
     * @return \App\Shifts\ShiftStatusManager
     */
    public function statusManager()
    {
        return new ShiftStatusManager($this);
    }

    /**
     * @return bool
     */
    public function isVerified()
    {
        return (bool)$this->verified;
    }

    /**
     * Returns true if a shift should no longer be modified
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->statusManager()->isReadOnly();
    }

    /**
     * Look for an EXACT duplicate.  This is different than the 'duplicate' flag which looks for potential duplicates.
     *
     * @return bool
     */
    public function hasDuplicate()
    {
        $query = self::where('checked_in_time', $this->checked_in_time)
            ->where('client_id', $this->client_id)
            ->where('caregiver_id', $this->caregiver_id);
        if ($this->id) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }

    /**
     * Handles adding and deleting issues based on an array of issues.
     *
     * @param array $issues
     * @return void
     */
    public function syncIssues($issues)
    {
        $new = collect($issues)->filter(function ($item) {
            return !isset($item['id']);
        });

        $existing = collect($issues)->filter(function ($item) {
            return isset($item['id']);
        });

        $ids = $existing->pluck('id');
        if (count($ids)) {
            // remove all issues with ids that aren't in the current array
            ShiftIssue::where('shift_id', $this->id)
                ->whereNotIn('id', $ids)
                ->delete();

            // update the existing issues in case they changed
            foreach ($existing as $item) {
                $issue = ShiftIssue::where('id', $item['id'])->first();
                if ($issue) {
                    $issue->update($item);
                }
            }
        } else {
            // clear
            ShiftIssue::where('shift_id', $this->id)->delete();
        }

        // create new issues from the issues that have no id
        foreach ($new as $item) {
            ShiftIssue::create(array_merge($item, ['shift_id' => $this->id]));
        }
    }

    /**
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage()
    {
        if ($this->costs()->hasPersistedCosts()) {
            return (float)$this->costs()->getPersistedCosts()->ally_pct;
        }

        if ($this->client) {
            return $this->client->getAllyPercentage();
        }

        // Default to CC fee
        return (float)config('ally.credit_card_fee');
    }

    /**
     * Get the rounded ally hourly rate
     *
     * @param $caregiverRate
     * @param $providerFee
     * @return float
     */
    public function getAllyHourlyRate($caregiverRate = null, $providerFee = null)
    {
        $providerFee = $providerFee ?: $this->provider_fee;
        $caregiverRate = $caregiverRate ?: $this->caregiver_rate;
        $amount = bcadd($providerFee, $caregiverRate, 2);
        return $this->getAllyFee($amount);
    }

    /**
     * Sync client goals data with the current shift.
     *
     * @param array $goals
     * @return Shift
     */
    public function syncGoals($goals)
    {
        // first reformat array to work with sync
        // and drop any values with empty comments.
        $data = [];
        foreach ($goals as $goalId => $comments) {
            if (empty($comments)) {
                continue;
            }

            $data[$goalId] = ['comments' => $comments];
        }

        $this->goals()->sync($data);

        return $this;
    }

    /**
     * Sync question answers to the shift.
     *
     * @param array $questions
     * @param array $answers
     * @return void
     */
    public function syncQuestions($questions, $answers)
    {
        $items = [];
        foreach ($questions as $q) {
            $answer = isset($answers[$q->id]) ? $answers[$q->id] : '';
            $items[$q->id] = ['answer' => $answer];
        }
        $this->questions()->sync($items);
    }

    /**
     * Check if the Shift has the given flag.
     *
     * @param string $flag
     * @return boolean
     */
    public function hasFlag($flag)
    {
        return in_array($flag, $this->flags);
    }

    /**
     * Add given flag to the Shift's flags, only if not added.
     *
     * @param string $flag
     * @return void
     */
    public function addFlag($flag)
    {
        if ($this->hasFlag($flag)) {
            return;
        }

        $this->shiftFlags()->create(['flag' => $flag]);
        $this->load('shiftFlags');
    }

    /**
     * Remove an existing flag from the shift
     *
     * @param $flag
     * @return bool
     */
    public function removeFlag($flag)
    {
        return (bool)$this->shiftFlags()->where('flag', $flag)->delete();
    }

    /**
     * Sync existing flags with a new array of generated flags
     *
     * @param array $flags
     */
    public function syncFlags(array $flags)
    {
        $removeFlags = array_diff($this->flags, $flags);
        $addFlags = array_diff($flags, $this->flags);
        foreach ($addFlags as $flag) {
            $this->addFlag($flag);
        }
        foreach ($removeFlags as $flag) {
            $this->removeFlag($flag);
        }
    }


    /**
     * @return string
     */
    public function getTimezone()
    {
        return Timezone::getTimezone($this->business_id);
    }

    protected function breakOutExpenses(): ?ShiftExpense
    {
        if ($this->other_expenses > 0) {
            return ShiftExpense::create([
                'shift_id' => $this->id,
                'name' => 'Other Expenses',
                'units' => 1,
                'rate' => $this->other_expenses,
                'notes' => str_limit($this->other_expenses_desc, 253, '..'),
            ]);
        }
        else if ($this->other_expenses < 0) {
            return ShiftExpense::create([
                'shift_id' => $this->id,
                'name' => 'Expense Adjustment',
                'units' => 1,
                'rate' => $this->other_expenses,
                'notes' => str_limit($this->other_expenses_desc, 253, '..'),
            ]);
        }
        return null;
    }

    protected function breakOutMileage(): ?ShiftExpense
    {
        if ($this->mileage > 0) {
            $mileageCalc = new MileageExpenseCalculator(null, $this->business, null, $this->mileage);
            return ShiftExpense::create([
                'shift_id' => $this->id,
                'name' => 'Mileage',
                'units' => $this->mileage,
                'rate' => $mileageCalc->getMileageRate(),
                'notes' => null,
            ]);
        }
        return null;
    }

    /**
     * Collect all applicable invoiceables of this type eligible for the client payment
     *
     * @param \App\Client $client
     * @param \Carbon\Carbon $endDateUtc
     * @return \Illuminate\Support\Collection
     */
    public function getItemsForPayment(Client $client, Carbon $endDateUtc): Collection
    {
        $query = new InvoiceableQuery($this);
        $shifts = $query->doesntHaveClientInvoice()
            ->where('client_id', $client->id)
            ->where('status', Shift::WAITING_FOR_INVOICE)
            ->where('checked_in_time', '<=', $endDateUtc->toDateTimeString())
            ->get();

        $collection = new Collection();
        foreach($shifts as $shift) {
            $expense = $shift->breakOutExpenses();
            if ($expense) $collection->push($expense);

            $mileage = $shift->breakOutMileage();
            if ($mileage) $collection->push($mileage);

            if ($shift->services->count()) {
                $collection = $collection->merge($shift->services);
            } else {
                $collection->push($shift);
            }
        }

        return $collection;
    }

    /**
     * Get the number of units to be invoiced
     *
     * @return float
     */
    public function getItemUnits(): float
    {
        return $this->fixed_rates ? 1 : $this->duration();
    }

    /**
     * Get the name of this item to display on the invoice
     *
     * @param string $invoiceModel
     * @return string
     */
    public function getItemName(string $invoiceModel): string
    {
        if ($service = $this->service) {
            /** @var Service $service */
            return $service->name . ' ' . $service->code;
        }

        // Fallback
        return $this->getItemGroup($invoiceModel);
    }

    /**
     * Get the group this item should be listed under on the invoice
     *
     * @param string $invoiceModel
     * @return string|null
     */
    public function getItemGroup(string $invoiceModel): ?string
    {
        $name = optional($this->client)->name() . ' - ' . optional($this->caregiver)->name();

        switch($invoiceModel) {
            case ClientInvoice::class:
                $name = optional($this->caregiver)->name();
                break;
            case CaregiverInvoice::class:
                $name = optional($this->client)->name();
                break;
        }

        return $this->checked_in_time->setTimezone($this->getTimezone())->format('F j g:iA')
            . '-' . $this->checked_out_time->setTimezone($this->getTimezone())->format('g:iA')
            . ': ' . $name;
    }

    /**
     * Get the date & time that this item's "service" occurred.   SHOULD respect the client/business timezone.
     * Note: This is used for sorting items on the invoice and determining payer allowances.
     *
     * @return string|null
     */
    public function getItemDate(): ?string
    {
        return $this->checked_in_time->setTimezone($this->getTimezone())->toDateTimeString();
    }

    /**
     * @return string|null
     */
    public function getItemNotes(): ?string
    {
        if (empty($this->activities)) {
            return null;
        }

        return str_limit($this->activities->implode('name', ', '), 252);
    }

    /**
     * Check if the client rate includes the ally fee (ex. true for shifts, false for expenses)
     *
     * @return bool
     */
    public function hasFeeIncluded(): bool
    {
        return true;
    }

    public function getShift(): ?Shift
    {
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * Get the client rate of this item (payment rate).  The total charged will be this rate multiplied by the units.
     *
     * @return float
     */
    public function getClientRate(): float
    {
        return $this->client_rate;
    }

    public function getCaregiver(): ?Caregiver
    {
        return $this->caregiver;
    }

    public function getCaregiverRate(): float
    {
        return $this->caregiver_rate;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    /**
     * Add an amount that has been invoiced to a payer
     *
     * @param \App\Billing\ClientInvoiceItem $invoiceItem
     * @param float $amount
     * @param float $allyFee  The value of $amount that represents the Ally Fee
     */
    public function addAmountInvoiced(ClientInvoiceItem $invoiceItem, float $amount, float $allyFee): void
    {
        if ($this->getAmountDue() === 0) {
            $this->statusManager()->ackClientInvoice();
        }
    }

    /**
     * Get the total billable hours of the shift based on
     * service including service breakouts.
     *
     * @param int|null $service_id
     * @return float
     */
    public function getBillableHours(?int $service_id = null) : float
    {
        if ($this->fixed_rates || ! empty($this->service_id)) {
            // actual hours shift
            if (! empty($service_id) && $service_id != $this->service_id) {
                // make sure service id matches the one on the model (or all).
                return 0;
            }
            return $this->duration(true);
        } else if ($this->services->isNotEmpty()) {
            // service breakout shift
            $services = $this->services;

            if (! empty($service_id)) {
                $services = $services->where('service_id', $service_id);
            }

            return floatval($services->sum('duration'));
        } else {
            return floatval(0);
        }
    }

    /**
     * Get the total billable hours for a specific day of the shift
     * based on service including service breakouts.
     *
     * @param \Carbon\Carbon $date
     * @param int|null $service_id
     * @return float
     */
    public function getBillableHoursForDay(Carbon $date, ?int $service_id = null) : float
    {
        $hours = $this->getBillableHours($service_id);
        if (count($this->getDateSpan()) === 1) { // only spans 1 day
            return $hours;
        }

        if ($this->services->isNotEmpty()) {
            // service breakout shift - return total shift hours for all days since this
            // is a complicated query and we want to protect them more than be 100% accurate
            return $hours;
        } else {
            // actual hours shift
            // TODO: this does not properly handle shifts that expand more than two days
            $tz = $this->client->getTimezone();
            $start = $this->checked_in_time->copy()->setTimezone($tz);
            $end = $this->checked_out_time->copy()->setTimezone($tz);

            if ($start->format('Ymd') === $date->format('Ymd')) {
                $minutes = $start->diffInMinutes($start->copy()->endOfDay());
                return $minutes === 0 ? 0 : ($minutes / 60);
            } else {
                $minutes = $end->copy()->startOfDay()->diffInMinutes($end);
                return $minutes === 0 ? 0 : ($minutes / 60);
            }
        }
    }

    /**
     * Get all of the dates that the shift exists on.
     *
     * @return array
     */
    public function getDateSpan() : array
    {
        // Convert shift dates to the client timezone so they are relative to ClientAuthorizations.
        $tz = $this->client->getTimezone();
        $start = $this->checked_in_time->copy()->setTimezone($tz)->setTime(0, 0, 0);
        $end = $this->checked_out_time->copy()->setTimezone($tz)->setTime(0, 0, 0);

        if ($start->format('Ymd') == $end->format('Ymd')) {  // same day
            return [$start];
        }

        // TODO: this does not properly handle shifts that expand more than two days
        return [$start, $end];
    }

    /**
     * Get service authorizations effected by this shift.
     *
     * @return iterable|null
     */
    public function getEffectedServiceAuthorizations() : ?iterable
    {
        $serviceIds = [$this->service_id];

        if (filled($this->services)) {
            $serviceIds = $this->services->pluck('id')->toArray();
        } else if (empty($this->service_id)) {
            return [];
        }

        $auths = collect([]);
        foreach ($this->getDateSpan() as $date) {
            $auths = $auths->merge($this->client->getActiveServiceAuths($date, $serviceIds));
        }

        return $auths->unique('id');
    }

    ///////////////////////////////////////////
    /// Query Scopes
    ///////////////////////////////////////////

    public function scopeWhereReadOnly($query)
    {
        return $query->whereIn('status', ShiftStatusManager::getReadOnlyStatuses());
    }

    public function scopeWherePending($query)
    {
        return $query->whereIn('status', ShiftStatusManager::getPendingStatuses());
    }

    public function scopeWhereAwaitingCharge($query)
    {
        return $query->whereIn('status', ShiftStatusManager::getAwaitingChargeStatuses());
    }

    public function scopeWhereAwaitingBusinessDeposit($query)
    {
        return $query->whereIn('status', ShiftStatusManager::getAwaitingBusinessDepositStatuses());
    }

    public function scopeWhereAwaitingCaregiverDeposit($query)
    {
        return $query->whereIn('status', ShiftStatusManager::getAwaitingCaregiverDepositStatuses());
    }

    public function scopeWhereConfirmed($query)
    {
        return $query->whereIn('status', ShiftStatusManager::getConfirmedStatuses());
    }

    public function scopeWhereUnconfirmed($query)
    {
        return $query->whereIn('status', ShiftStatusManager::getUnconfirmedStatuses());
    }

    public function scopeWhereTelephonyVerified($query)
    {
        return $query->where('verified', 1)
            ->whereNotNull('checked_in_number');
    }

    public function scopeWhereMobileVerified($query)
    {
        return $query->where('verified', 1)
            ->whereNotNull('checked_in_latitude');
    }

    /**
     * A query scope for filtering invoicables by related caregiver IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $caregiverIds
     * @return void
     */
    public function scopeForCaregivers(Builder $builder, array $caregiverIds)
    {
        $builder->whereIn('caregiver_id', $caregiverIds);
    }

    /**
     * Gets shifts that belong to the given caregiver id, ignoring an empty value.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed $caregiver
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForCaregiver($query, $caregiver)
    {
        if (empty($caregiver)) {
            return $query;
        }

        return $query->where('caregiver_id', $caregiver);
    }

    /**
     * Gets shifts that belong to the given client id, ignoring an empty value.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed $client
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForClient($query, $client)
    {
        if (empty($client)) {
            return $query;
        }

        return $query->where('client_id', $client);
    }

    /**
     * Gets shifts that belong to the given client ids only.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param iterable $clients
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForClients($query, $clients)
    {
        return $query->whereIn('client_id', $clients);
    }

    /**
     * Gets shifts that are checked in between given given start and end dates.
     * Automatically applies timezone transformation.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $start
     * @param string $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        if (empty($start) || empty($end)) {
            return $query;
        }

        $startDate = (new Carbon($start . ' 00:00:00', 'America/New_York'))->setTimezone('UTC');
        $endDate = (new Carbon($end . ' 23:59:59', 'America/New_York'))->setTimezone('UTC');
        return $query->whereBetween('checked_in_time', [$startDate, $endDate]);
    }

    /**
     * Search shifts that contain any of the provided flags
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $flags
     */
    public function scopeWhereFlagsIn(Builder $query, array $flags)
    {
        $query->whereHas('shiftFlags', function ($q) use ($flags) {
            $q->whereIn('flag', $flags);
        });
    }

}
