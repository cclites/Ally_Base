<?php
namespace App\Billing;

use App\AuditableModel;
use App\Caregiver;
use App\Client;

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