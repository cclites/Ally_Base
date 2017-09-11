<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'schedule_activities');
    }

    public function exceptions()
    {
        return $this->hasMany(ScheduleException::class);
    }
}
