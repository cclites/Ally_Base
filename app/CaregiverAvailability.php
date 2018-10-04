<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CaregiverAvailability extends Model
{
    protected $table = 'caregiver_availability';
    protected $guarded = ['id'];
    public $incrementing = false;

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
