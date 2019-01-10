<?php
namespace App\Billing;

use App\AuditableModel;
use App\Caregiver;
use App\Client;

/**
 * App\Billing\ClientRate
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client $client
 * @property-read \App\Billing\Payer $payer
 * @property-read \App\Billing\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class ClientRate extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'payer_id' => 'int',
        'service_id' => 'int',
        'caregiver_id' => 'int',
        'caregiver_hourly_rate' => 'float',
        'caregiver_fixed_rate' => 'float',
        'client_hourly_rate' => 'float',
        'client_fixed_rate' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
    }

    function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }
}