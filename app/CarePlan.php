<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\CarePlan
 *
 * @property int $id
 * @property string $name
 * @property int $business_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business $business
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan withoutTrashed()
 * @mixin \Eloquent
 */
class CarePlan extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'care_plans';

    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /**
     * A Care Plan has many Activities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'care_plan_activities');
    }

    /**
     * A Care Plan belongs to a Business.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * A Care Plan belongs to a Client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * A Care Plan has many schedules.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schedules()
    {
        return $this->HasMany(Schedule::class);
    }

    /**
     * A Can Plan has many future Schedules.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function futureSchedules()
    {
        return $this->schedules()
            ->where('starts_at', '>', Carbon::now());
    }

    /**
     * Remove reference to the current Care Plan from all future schedules.
     *
     * @return $this
     */
    public function removeFromFutureSchedules()
    {
        $this->futureSchedules()->update([
            'care_plan_id' => null,
        ]);

        return $this;
    }
}