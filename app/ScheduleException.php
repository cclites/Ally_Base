<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    protected $fillable = ['date'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
