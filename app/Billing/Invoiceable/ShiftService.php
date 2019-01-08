<?php
namespace App\Billing\Invoiceable;

use App\AuditableModel;
use App\Billing\Service;
use App\Caregiver;
use App\Billing\Payer;
use App\Shift;

class ShiftService extends AuditableModel
{
    protected $guarded = ['id'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
    }
}