<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskEditHistory extends Model
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
