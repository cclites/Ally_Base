<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimesheetEntry extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['duration'];

    protected $dates = ['checked_in_time', 'checked_out_time'];

    protected $with = ['activities'];

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
     * Convert the start_time to valid checked in date.
     *
     * @param [type] $value
     * @return void
     */
    public function setStartTimeAttribute($value)
    {
        $this->attributes['checked_in_time'] = Carbon::parse($this->date . ' ' . $value);
    }

    /**
     * Convert the end_time to valid checked out date.
     *
     * @param [type] $value
     * @return void
     */
    public function setEndTimeAttribute($value)
    {
        $this->attributes['checked_out_time'] = Carbon::parse($this->date . ' ' . $value);
    }
}
