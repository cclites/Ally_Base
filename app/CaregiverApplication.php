<?php

namespace App;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

class CaregiverApplication extends Model
{
    protected $guarded = ['id'];

    public function position()
    {
        return $this->belongsTo(CaregiverPosition::class, 'caregiver_position_id');
    }

    public function status()
    {
        return $this->belongsTo(CaregiverApplicationStatus::class, 'caregiver_application_status_id');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////


    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Encrypt ssn on entry
     *
     * @param $value
     */
    public function setSsnAttribute($value)
    {
        $this->attributes['ssn'] = Crypt::encrypt($value);
    }

    /**
     * Decrypt ssn on retrieval
     *
     * @return null|string
     */
    public function getSsnAttribute()
    {
        return empty($this->attributes['ssn']) ? null : Crypt::decrypt($this->attributes['ssn']);
    }
}
