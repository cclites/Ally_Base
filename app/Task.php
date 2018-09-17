<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

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
    protected $dates = ['due_date'];

    /**
     * The attributes to always append to the model.
     *
     * @var array
     */
    protected $appends = ['last_edit'];
    
    /**
     * The relationships that should be auto-loaded.
     *
     * @var array
     */
    protected $with = ['editHistory'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function($task) {
            if (!isset($task->creator_id)) {
                $task->creator_id = auth()->user()->id;
            }
            if (!isset($task->business_id)) {
                $task->business_id = activeBusiness()->id;
            }
        });

        // keep log of task edits
        static::saved(function ($task) {
            $task->editHistory()->create(['user_id' => auth()->user()->id]);
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
}
