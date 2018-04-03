<?php

namespace App;

use App\Businesses\Timezone;
use App\Events\ShiftCreated;
use App\Events\ShiftModified;
use App\Shifts\CostCalculator;
use App\Shifts\ShiftStatusManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Shift
 *
 * @property int $id
 * @property int|null $caregiver_id
 * @property int|null $client_id
 * @property int|null $business_id
 * @property int $checked_in
 * @property \Carbon\Carbon|null $checked_in_time
 * @property float|null $checked_in_latitude
 * @property float|null $checked_in_longitude
 * @property string|null $checked_in_number evv phone number
 * @property \Carbon\Carbon|null $checked_out_time
 * @property float|null $checked_out_latitude
 * @property float|null $checked_out_longitude
 * @property string|null $checked_out_number evv phone number
 * @property string|null $caregiver_comments
 * @property string|null $hours_type
 * @property float $mileage
 * @property float $other_expenses
 * @property int $verified
 * @property int|null $schedule_id
 * @property int $all_day
 * @property float $caregiver_rate
 * @property float $provider_fee
 * @property string|null $status
 * @property int|null $payment_id
 * @property string|null $other_expenses_desc
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client|null $client
 * @property-read \App\ShiftCostHistory $costHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SystemException[] $exceptions
 * @property-read mixed $duration
 * @property-read mixed $read_only
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftIssue[] $issues
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftActivity[] $otherActivities
 * @property-read \App\Payment|null $payment
 * @property-read \App\Schedule|null $schedule
 * @property-read \App\Signature $signature
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftStatusHistory[] $statusHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAllDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingBusinessDeposit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingCaregiverDeposit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingCharge()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereConfirmed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereOtherExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereOtherExpensesDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift wherePending()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereReadOnly()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereUnconfirmed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereVerified($value)
 * @mixin \Eloquent
 */
class Shift extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['duration', 'readOnly'];
    protected $dates = ['checked_in_time', 'checked_out_time', 'signature'];

    ///////////////////////////////////////////
    /// Events
    ///////////////////////////////////////////

    protected $dispatchesEvents = [
        'created' => ShiftCreated::class,
        'updated' => ShiftModified::class,
    ];

    ///////////////////////////////////////
    /// Shift Statuses
    ///////////////////////////////////////

    const CLOCKED_IN = 'CLOCKED_IN';
    const CLOCKED_OUT = 'CLOCKED_OUT'; // not currently used
    const WAITING_FOR_CONFIRMATION = 'WAITING_FOR_CONFIRMATION';  // Unconfirmed shift that needs to be approved
    const WAITING_FOR_AUTHORIZATION = 'WAITING_FOR_AUTHORIZATION';  // Confirmed shift that needs to be authorized for payment
    const WAITING_FOR_CHARGE = 'WAITING_FOR_CHARGE';  // Authorized shift that is waiting for batch processing
    // Read-only statuses from here down (see isReadOnly())
    const WAITING_FOR_PAYOUT = 'WAITING_FOR_PAYOUT';  // Charged shift that is waiting for payout (settlement)
    const PAID_BUSINESS_ONLY = 'PAID_BUSINESS_ONLY'; // Shift that failed payment to the caregiver, but paid successfully to the business
    const PAID_CAREGIVER_ONLY = 'PAID_CAREGIVER_ONLY'; // Shift that failed payment to the business, but paid successfully to the caregiver
    const PAID_BUSINESS_ONLY_NOT_CHARGED = 'PAID_BUSINESS_ONLY_NOT_CHARGED'; // Shift that failed payment to the caregiver, paid successfully to the business, but still requires payment from the client
    const PAID_CAREGIVER_ONLY_NOT_CHARGED = 'PAID_CAREGIVER_ONLY_NOT_CHARGED'; // Shift that failed payment to the business, paid successfully to the caregiver, but still requires payment from the client
    const PAID_NOT_CHARGED  = 'PAID_NOT_CHARGED';  // Shift that was paid out to both business & caregiver but still requires payment from the client
    const PAID  = 'PAID';  // Shift that has been successfully charged and paid out (FINAL)

    //////////////////////////////////////
    /// Relationship Methods
    //////////////////////////////////////

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function deposits()
    {
        return $this->belongsToMany(Deposit::class,'deposit_shifts');
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

    public function exceptions()
    {
        return $this->morphMany(SystemException::class, 'reference');
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

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getTimezoneAttribute()
    {
        return Timezone::getTimezone($this->business_id);
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

    //////////////////////////////////////
    /// Other Methods
    //////////////////////////////////////

    /**
     * Return the number of hours worked
     *
     * @return float
     */
    public function duration()
    {
        $date1 = new Carbon($this->checked_in_time);
        if ($this->checked_out_time) {
            $date2 = new Carbon($this->checked_out_time);
        } else {
            $date2 = new Carbon();
        }

        $duration = round($date1->diffInMinutes($date2) / 60, 2);
        return number_format(floor(round($duration * 4)) / 4, 2);
    }

    /**
     * Get the scheduled end time of the shift
     *
     * @return Carbon
     */
    public function scheduledEndTime()
    {
        if (!$this->schedule) {
            // Return now if no schedule
            return Carbon::now();
        }

        return $this->schedule->getEndDateTime();
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
        return (bool) $this->verified;
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
}
