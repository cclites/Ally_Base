<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CaregiverPosition extends Model
{
    protected $guarded = ['id'];

    public function caregiverApplications()
    {
        return $this->hasMany(CaregiverApplication::class);
    }
}
