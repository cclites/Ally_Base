<?php
namespace App;

/**
 * App\TaskEditHistory
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read void $edited_by
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TaskEditHistory query()
 */
class TaskEditHistory extends BaseModel
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'task_edit_history';

    /**
     * The attributes that are not mass-assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relationships to auto-load with the model.
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * The attributes that should always be appended to the model.
     *
     * @var array
     */
    protected $appends = ['edited_by'];

    /**
     * Get the editing user relation.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the name of the user.
     *
     * @return void
     */
    public function getEditedByAttribute()
    {
        return $this->user->name;
    }
}
