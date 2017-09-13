<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    protected $fillable = ['date'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
