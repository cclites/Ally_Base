<?php
namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\ClientGoal
 *
 * @property int $id
 * @property int $client_id
 * @property string $question
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $track_goal_progress
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal tracked($areTracked = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereTrackGoalProgress($value)
 * @property-read int|null $audits_count
 * @property-read int|null $shifts_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\ClientGoal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\ClientGoal withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\ClientGoal withoutTrashed()
 */
class ClientGoal extends AuditableModel
{
    use SoftDeletes;

    protected $table = 'client_goals';

    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /**
     * A ClientGoal belongs to a Client
     *
     * @return BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * A ClientGoal can have many shifts.
     *
     * @return BelongsToMany
     */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class);
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    public function scopeTracked(Builder $builder, bool $areTracked = true)
    {
        $builder->where('track_goal_progress', $areTracked);
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
            'question' => $faker->sentence.'?',
        ];
    }
}
