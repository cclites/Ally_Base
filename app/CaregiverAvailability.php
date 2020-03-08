<?php

namespace App;

use App\Events\CaregiverAvailabilityChanged;
use phpDocumentor\Reflection\Types\Boolean;

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

    const CONFLICT_REASON = 'Caregiver changed days of availability';

    //Completely arbitrary threshold to limit number of retrieved shifts.
    const THRESHOLD = 14;

    public function updatedByUser()
    {
        //This is a strange relationship. Why no caregiver_id? Not obvious as to
        //why an office user would own a caregiver_availability record
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * When CG removes available days, make sure CG was not scheduled on those days
     *
     * @param int $caregiverId
     * @param array $availability //represents days of the week as strings
     * @return bool
     */
    public static function checkRemovedAvailableDaysConflict(Caregiver $caregiver, array $availability): bool
    {
        $hasConflict = false;

        //limit checks to today and later
        $today = \Carbon::today()->startOfDay();

        $businessId = $caregiver->businesses->first()->id;

        //NOTE: the threshold used here is to prevent grabbing every single caregiver
        //for all of eternity. Not sure if that is the right choice.
        $schedules = Schedule::where('caregiver_id', $caregiver->id)
            ->where('starts_at', '>=', $today)
            ->where('business_id', $businessId)
            ->select('id', 'starts_at')
            ->take(self::THRESHOLD)
            ->get();

        collect($availability)->map(function($day) use($caregiver,$today, $schedules, &$hasConflict, $businessId){

            $schedules->map(function($schedule) use($day,$caregiver, &$hasConflict, $businessId){
                $startsAt = \Carbon::instance(new \DateTimeImmutable($schedule->starts_at));

                if($startsAt->is(ucfirst($day)))
                {
                    $hasConflict = true;

                    \DB::table('caregiver_availability_conflict')->insert([
                        'caregiver_id'=>$caregiver->id,
                        'schedule_id'=>$schedule->id,
                        'business_id'=>$businessId,
                        'starts_at'=>$schedule->starts_at,
                        'reason'=>self::CONFLICT_REASON
                    ]);
                }

                return null;
            });

        })->flatten(1)
            ->values()
            ->unique();

        return $hasConflict;
    }

    /**
     * See if days have been removed from available days
     *
     * @param $newAvailabilities
     * @param $storedAvailabilities
     * @return array
     */
    public static function arrayDiffAvailability($newAvailabilities, Caregiver $caregiver): array
    {
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        $arrayDiff = [];

        foreach($days as $day){

            if($newAvailabilities[$day] === 0 && $newAvailabilities[$day] !== $caregiver->ability[$day]){
                $arrayDiff[] = $day;
            }
        }

        return $arrayDiff;
    }

    /**
     *  Get day of the week as an int. Days of week are zero-based
     * @param $day
     * @return false|float
     */
    public function getStartDayAsInt($day){
        return floor($day / 24);
    }

    /**
     * get hour of day in 12 hour format
     * @param $day
     * @return int
     */
    public function getHourAs12($day){
        return ($day % (12)) + 1;
    }

    /**
     * Get label for the given time
     *
     * @param $day
     * @return string
     */
    public function getAmPmLabel($day){
        return $day % 24 > 12 ? " PM" : " AM";
    }

    public function convertToDay($day){
        $days = ['sunday','monday','tuesday','wednesday','thursday','friday', 'saturday'];
        return $days[$day];
    }

    //TODO: Convert day/hour to magic numbers
    /**
     * What data gets here?
     *   - array of day name literals as string.
     *   - shift_start_time
     *   - shift_start_end
     *
     * What gets returned?
     *   - array of integers, ie. 'magic_numbers'
     */

    //TODO: Convert magic numbers to day/hr return as array
    /**
     * What data gets here?
     *  - array of ints
     *
     */

    //TODO: Make a migration to convert available_start_time, available_end_time, and day to magic_days


    /**
     * @param array $days
     * @return string
     */
    public function serializeDays(array $days): string
    {
        return serialize($days);
    }

    /**
     * @param string $days
     * @return array
     */
    public function unserializeDays(string $days): array
    {
        return unserialize($days);
    }


}
