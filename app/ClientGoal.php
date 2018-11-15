<?php
namespace App;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientGoal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientGoal extends AuditableModel
{
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
}
