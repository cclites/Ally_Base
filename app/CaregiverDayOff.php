<?php

namespace App;

use App\Events\CaregiverAvailabilityChanged;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * App\CaregiverDayOff
 *
 * @property int $id
 * @property int $caregiver_id
 * @property string $start_date
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $end_date
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverDayOff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverDayOff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverDayOff query()
 * @mixin \Eloquent
 */
class CaregiverDayOff extends AuditableModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'caregiver_days_off';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    const CONFLICT_REASON = 'Caregiver scheduled a day off';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************


    /**
     * When CG schedules vacation days, checks to make sure CG wasn't scheduled
     * on those days.
     *
     * @param int $caregiverId
     * @param array $daysOff
     * @return bool
     */
    public static function checkAddedVacationConflict(Caregiver $caregiver, array $daysOff): bool
    {
        $today = \Carbon::today()->startOfDay();
        $hasConflict = false;
        $businessId = $caregiver->businesses()->first()->id;

        $scheduled = collect($daysOff)->map(function($day) use($caregiver, $today, $businessId){

                $startDate = \Carbon::createFromFormat('Y-m-d', $day['start_date'])->startOfDay();
                $endDate = \Carbon::createFromFormat('Y-m-d', $day['end_date'])->endOfDay();

                return Schedule::whereBetween('starts_at', [$startDate, $endDate])
                    ->where('starts_at', '>=', $today)
                    ->where('caregiver_id', $caregiver->id)
                    ->where('business_id', $businessId)
                    ->select('id', 'starts_at')
                    ->get();

        })->flatten(1)
        ->values()
        ->unique();

        if($scheduled->count()){
            $hasConflict = true;
        }

        foreach($scheduled as $schedule){
            \DB::table('caregiver_availability_conflict')->insert([
                'caregiver_id'=>$caregiver->id,
                'schedule_id'=>$schedule->id,
                'business_id'=>$businessId,
                'starts_at'=>$schedule->starts_at,
                'reason'=>self::CONFLICT_REASON
            ]);
        }

        return $hasConflict;
    }

    /**
     * @param $newDays
     * @param $storedDaysOff
     * @return array
     */
    public static function arrayDiffCustom($newDays, Caregiver $caregiver): array
    {
        $daysOff = $caregiver->daysOff->map(function (\App\CaregiverDayOff $dayOff) {
            return [
                'start_date' => $dayOff->start_date,
                'end_date' => $dayOff->end_date,
                'description' => $dayOff->description,
            ];
        })->toArray();

        $index = 0;

        foreach ($newDays as $day) {
            foreach($daysOff as $dayOff){
                if($day === $dayOff){
                    unset($newDays[$index]);
                }
            }
            $index++;
        }

        return $newDays;
    }
}
