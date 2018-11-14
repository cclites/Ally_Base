<?php
namespace App;

/**
 * App\ScheduleException
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Schedule $schedule
 * @mixin \Eloquent
 */
class ScheduleException extends AuditableModel
{

    protected $fillable = ['date'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
