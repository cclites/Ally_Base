<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\TaskAssigned;
use Carbon\Carbon;

/**
 * App\Task
 *
 * @property int $id
 * @property int $business_id
 * @property int $creator_id
 * @property string $name
 * @property string|null $notes
 * @property int $assigned_user_id
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $completed_at
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User|null $assignedUser
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\OfficeUser $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TaskEditHistory[] $editHistory
 * @property-read mixed $assigned_type
 * @property-read \TaskEditHistory $last_edit
 * @property-write mixed $completed
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task assignedTo($user_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task createdBy($user_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Task onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereAssignedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Task withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Task withoutTrashed()
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @property-read int|null $edit_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Task query()
 */
class Task extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;
    use SoftDeletes;
    
    /**
     * The attributes that should not be mass-assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['due_date', 'completed_at'];

    /**
     * The attributes to always append to the model.
     *
     * @var array
     */
    protected $appends = ['last_edit', 'assigned_type'];
    
    /**
     * The list of attributes that should be specifically cast.
     * 
     * @var array
     */
    protected $casts = [
        'business_id' => 'integer',
        'creator_id' => 'integer',
        'assigned_user_id' => 'integer',
    ];

    /**
     * @var string
     */
    protected $orderedColumn = 'due_date';

    /**
     * The relationships that should be auto-loaded.
     *
     * @var array
     */
    protected $with = ['editHistory', 'assignedUser', 'creator'];

    protected static function boot()
    {
        parent::boot();

        // always set creator & business
        static::creating(function($task) {
            if (!isset($task->creator_id)) {
                $task->creator_id = auth()->user()->id;
            }
            if (!isset($task->business_id)) {
                $task->business_id = activeBusiness()->id;
            }
        });

        static::saved(function ($task) {
            // throw event when assigned task is changed
            if ($task->isDirty('assigned_user_id') && $task->assigned_user_id != null) {
                event(new TaskAssigned($task));
            }

            // keep log of task edits
            if (auth()->check()) {
                $task->editHistory()->create(['user_id' => auth()->user()->id]);
            }
        });
    }

    /**
     * The user assigned to the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id');
    }

    /**
     * The office user that created the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(OfficeUser::class, 'creator_id', 'id');
    }

    /**
     * Get the tasks edit history relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function editHistory()
    {
        return $this->hasMany(TaskEditHistory::class, 'task_id', 'id')->latest();
    }
    
    /**
     * Get the last edit record.
     *
     * @return TaskEditHistory
     */
    public function getLastEditAttribute()
    {
        return $this->editHistory
            ->first();
    }

    /**
     * Get the owning business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Return Tasks created by logged in user.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     * @param int|string|null $user_id
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function scopeCreatedBy($query, $user_id)
    {
        if (empty($user_id)) {
            return $query;
        }

        return $query->where('creator_id', $user_id);
    }

    /**
     * Return Tasks assigned to the logged in user.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     * @param int|string|null $user_id
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function scopeAssignedTo($query, $user_id)
    {
        if (empty($user_id)) {
            return $query;
        }

        return $query->where('assigned_user_id', $user_id);
    }

    /**
     * Mark the Task as completed.
     *
     * @return void
     */
    public function markComplete()
    {
        $this->update(['completed_at' => Carbon::now()]);
    }

    /**
     * Set the completed_at timestamp via a completed boolean attribute.
     *
     * @param bool $val
     * @return void
     */
    public function setCompletedAttribute($val)
    {
        if (empty($this->completed_at) && $val == 1) {
            $this->completed_at = Carbon::now();
        } else if (!empty($this->completed_at) && $val == 0) {
            $this->completed_at = null;
        }
    }

    public function getAssignedTypeAttribute()
    {
        if (! empty($this->assignedUser)) {
            switch ($this->assignedUser->role_type) {
                case 'office_user':
                    return 'Staff';
                case 'caregiver':
                    return 'Caregiver';
                default:
                    return null;
            }
        }

        return null;
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
            'name' => $faker->word,
            'notes' => $faker->sentence,
        ];
    }
}
