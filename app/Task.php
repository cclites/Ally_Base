<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Events\TaskAssigned;
use Carbon\Carbon;

class Task extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
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
    protected $appends = ['last_edit'];
    
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
                // dd('wut');
                $task->editHistory()->create(['user_id' => auth()->user()->id]);
            }
        });
    }

    /**
     * The office user assigned to the task.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedUser()
    {
        return $this->belongsTo(OfficeUser::class, 'assigned_user_id', 'id');
    }

    /**
     * The office user that created the task.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(OfficeUser::class, 'creator_id', 'id');
    }

    /**
     * Get the tasks edit history relation.
     *
     * @return Illuminate\Database\Eloquent\Relations\hasMany
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
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
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
}
