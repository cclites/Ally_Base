<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleNote extends Model
{
    protected $table = 'schedule_notes';
    protected $guarded = ['id'];

    public function __toString()
    {
        return $this->note;
    }

    public function schedules() {
        return $this->hasMany(Schedule::class, 'note_id');
    }
}
