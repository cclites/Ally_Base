<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
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
}
