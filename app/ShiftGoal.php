<?php
namespace App;

/**
 * App\ShiftGoal
 *
 * @property int $id
 * @property int $shift_id
 * @property int $client_goal_id
 * @property string $comments
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \App\ClientGoal $goal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal whereClientGoalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftGoal query()
 */
class ShiftGoal extends AuditableModel
{
    
    protected $table = 'shift_goals';

    protected $guarded = ['id'];

    protected $with = ['goal'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /**
     * A ShiftGoal belongs to a Client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * A ShiftGoal has one Goal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function goal()
    {
        return $this->hasOne(ClientGoal::class, 'id', 'client_goal_id');
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
            'comments' => $faker->sentence,
        ];
    }
}
