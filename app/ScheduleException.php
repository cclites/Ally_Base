<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
