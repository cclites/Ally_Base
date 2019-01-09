<?php
namespace App\Billing\Invoiceable;

use App\AuditableModel;
use App\Billing\Service;
use App\Caregiver;
use App\Billing\Payer;
use App\Shift;

/**
 * App\Billing\Invoiceable\ShiftService
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Billing\Payer $payer
 * @property-read \App\Billing\Service $service
 * @property-read \App\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
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