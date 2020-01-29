<?php

namespace App;

/**
 * App\CaregiverAvailability
 *
 * @property int $id
 * @property int $monday
 * @property int $tuesday
 * @property int $wednesday
 * @property int $thursday
 * @property int $friday
 * @property int $saturday
 * @property int $sunday
 * @property int $morning
 * @property int $afternoon
 * @property int $evening
 * @property int $night
 * @property int $live_in
 * @property int $minimum_shift_hours
 * @property int $maximum_shift_hours
 * @property int $maximum_miles
 * @property int|null $updated_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\User|null $updatedByUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereAfternoon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereEvening($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereFriday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereLiveIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereMaximumMiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereMaximumShiftHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereMinimumShiftHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereMonday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereMorning($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereNight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereSaturday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereSunday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereThursday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereTuesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability whereWednesday($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverAvailability query()
 */
class CaregiverAvailability extends AuditableModel
{
    protected $table = 'caregiver_availability';
    protected $guarded = ['id'];
    public $incrementing = false;

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
