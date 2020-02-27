<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OccAccDeductible extends Model
{
    protected $guarded = [];


    public function caregiver()
    {
        return $this->belongsTo('App\Caregiver');
    }

    public function caregiverInvoice()
    {
        return $this->belongsTo('App\Billing\CaregiverInvoice');
    }

    public function shifts()
    {
        return $this->hasMany( 'App\Shift' );
    }

}
