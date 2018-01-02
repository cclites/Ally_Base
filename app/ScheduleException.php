<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ScheduleException
 *
 * @property int $id
 * @property int $schedule_id
 * @property string $date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Schedule $schedule
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ScheduleException extends Model
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
