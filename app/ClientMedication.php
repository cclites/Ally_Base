<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ClientMedication extends Model
{
    protected $guarded = ['id'];

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = Crypt::encrypt($value);
    }

    public function getTypeAttribute()
    {
        return empty($this->attributes['type']) ? null : Crypt::decrypt($this->attributes['type']);
    }

    public function setDoseAttribute($value)
    {
        $this->attributes['dose'] = Crypt::encrypt($value);
    }

    public function getDoseAttribute()
    {
        return empty($this->attributes['dose']) ? null : Crypt::decrypt($this->attributes['dose']);
    }

    public function setFrequencyAttribute($value)
    {
        $this->attributes['frequency'] = Crypt::encrypt($value);
    }

    public function getFrequencyAttribute()
    {
        return empty($this->attributes['frequency']) ? null : Crypt::decrypt($this->attributes['frequency']);
    }

}
