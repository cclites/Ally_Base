<?php
namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * App\CarePlan
 *
 * @property int $id
 * @property string $name
 * @property int $business_id
 * @property int|null $client_id
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan withoutTrashed()
 * @mixin \Eloquent
 * @property-read int|null $activities_count
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $futureSchedules
 * @property-read int|null $future_schedules_count
 * @property-read int|null $schedules_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan query()
 */
class CarePlan extends AuditableModel
{
    use SoftDeletes;

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

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

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
            'name' => $faker->name,
            'notes' => $faker->sentence,
        ];
    }
}