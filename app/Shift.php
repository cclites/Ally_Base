<?php

namespace App;

use App\Scheduling\CostCalculator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

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
        }
        else {
            $date2 = new Carbon();
        }

        return round($date1->diffInMinutes($date2) / 60, 2);
    }

    public function scheduledEndTime()
    {
        $shiftStart = new Carbon($this->checked_in_time);
        $scheduleStart = new Carbon($this->schedule->time);
        if ($scheduleStart->diffInMinutes($shiftStart) > 60 && $scheduleStart > $shiftStart) {
            $scheduleStart->subDay();
        }
        $end = $scheduleStart->copy()->addMinutes($this->schedule->duration);
        return $end;
    }

    /**
     * Return the number of hours remaining in the shift (as scheduled)
     *
     * @return int
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
     * @return \App\Scheduling\CostCalculator
     */
    public function costs()
    {
        return new CostCalculator($this);
    }

    public function isVerified()
    {
        return (bool) $this->verified;
    }
}
