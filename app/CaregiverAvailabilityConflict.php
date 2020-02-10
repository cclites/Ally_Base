<?php

namespace App;

use App\Business;
use App\Caregiver;
use App\Schedule;

class CaregiverAvailabilityConflict extends AuditableModel
{
    protected $table = "caregiver_availability_conflict";

    public function business(){
        return $this->belongsTo(Business::class);
    }

    public function caregiver(){
        return $this->belongsTo(Caregiver::class);
    }

    public function schedule(){
        return $this->belongsTo(Schedule::class);
    }
}
