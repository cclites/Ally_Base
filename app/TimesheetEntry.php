<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

class TimesheetEntry extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    protected $appends = ['duration', 'activities'];

    protected $dates = ['checked_in_time', 'checked_out_time'];

    protected static function boot() {
        parent::boot();
    
        static::saving(function($model) {
            unset($model->date);
        });
    }
    
    //////////////////////////////////////
    /// Relationship Methods
    //////////////////////////////////////

    /**
     * A TimesheetEntry belongs to a Timesheet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function timesheet()
    {   
        return $this->belongsTo(Timesheet::class);
    }

    /**
     * A TimesheetEntry has many Activities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'timesheet_entry_activities')
            ->orderBy('code');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Return the number of hours worked.
     *
     * @return float
     */
    public function getDurationAttribute()
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
     * Get flattend array of activity attributes.
     *
     * @return void
     */
    public function getActivitiesAttribute()
    {
        return $this->activities()
            ->get()
            ->pluck('id')
            ->toArray();
    }
}
