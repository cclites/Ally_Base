<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    public $timestamps = false;

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
        return $this->belongsToMany(Activity::class)->withPivot(['completed']);
    }

    public function issues()
    {
        return $this->hasMany(ShiftIssue::class);
    }

    public function duration()
    {
        if (!$this->checked_out_time) return false;

        $date1 = new Carbon($this->checked_in_time);
        $date2 = new Carbon($this->checked_out_time);

        return round($date1->diffInMinutes($date2) / 60, 2);
    }

    public function isVerified()
    {
        return (bool) $this->verified;
    }
}
