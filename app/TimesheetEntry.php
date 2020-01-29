<?php
namespace App;

use Carbon\Carbon;

/**
 * App\TimesheetEntry
 *
 * @property int $id
 * @property int $timesheet_id
 * @property \Carbon\Carbon $checked_in_time
 * @property \Carbon\Carbon $checked_out_time
 * @property float $mileage
 * @property float $other_expenses
 * @property string|null $caregiver_comments
 * @property float $caregiver_rate
 * @property float $client_rate
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read void $activities
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read float $duration
 * @property-read \App\Timesheet $timesheet
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereCaregiverComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereCheckedInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereCheckedOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereOtherExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereTimesheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property float|null $provider_fee
 * @property-read int|null $activities_count
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TimesheetEntry query()
 */
class TimesheetEntry extends AuditableModel
{
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
    
    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('caregiver_comments');
    }

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'caregiver_comments' => $faker->sentence,
        ];
    }
}
