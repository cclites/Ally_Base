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
    public static function checkAddedVacationConflict(int $caregiverId, array $daysOff): boolean
    {
        $today = \Carbon::today()->startOfDay();
        $hasConflict = false;

        $scheduled = collect($daysOff)->map(function($day) use($caregiverId, $today){

                $startDate = \Carbon::createFromFormat('Y-m-d', $day['start_date'])->startOfDay();
                $endDate = \Carbon::createFromFormat('Y-m-d', $day['end_date'])->endOfDay();

                return Schedule::whereBetween('starts_at', [$startDate, $endDate])
                    ->where('starts_at', '>=', $today)
                    ->where('caregiver_id', $caregiverId)
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
                'caregiver_id'=>$caregiverId,
                'schedule_id'=>$schedule->id,
                'starts_at'=>$schedule->starts_at,
                'reason'=>self::CONFLICT_REASON
            ]);
        }

        return $hasConflict;
    }
}
