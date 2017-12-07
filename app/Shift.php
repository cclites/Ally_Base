<?php

namespace App;

use App\Shifts\CostCalculator;
use App\Shifts\ShiftStatusManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $appends = ['roundedShiftLength', 'readOnly'];
    protected $dates = ['checked_in_time', 'checked_out_time', 'signature'];

    ///////////////////////////////////////
    /// Shift Statuses
    ///////////////////////////////////////

    const CLOCKED_IN = 'CLOCKED_IN';
    const CLOCKED_OUT = 'CLOCKED_OUT'; // not currently used
    const WAITING_FOR_APPROVAL = 'WAITING_FOR_APPROVAL';  // Unverified shift that needs to be approved
    const WAITING_FOR_AUTHORIZATION = 'WAITING_FOR_AUTHORIZATION';  // Verified shift that needs to be authorized for payment
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
        return $this->belongsTo(Client::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
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

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getRoundedShiftLengthAttribute()
    {
        return $this->duration();
    }

    public function getReadOnlyAttribute()
    {
        return $this->isReadOnly();
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
        $shiftStart = new Carbon($this->checked_in_time);
        $scheduleStart = Carbon::now()->setTimeFromTimeString($this->schedule->time);

        if ($scheduleStart->diffInMinutes($shiftStart) > 60 && $scheduleStart > $shiftStart) {
            $scheduleStart->subDay();
        }

        $end = $scheduleStart->copy()->addMinutes($this->schedule->duration);
        return $end;
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
    public function status()
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
        return $this->status()->isReadOnly();
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
}
