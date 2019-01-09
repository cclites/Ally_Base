<?php
namespace App\Billing;

use App\AuditableModel;
use App\Client;

class ClientAuthorization extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'service_id' => 'int',
        'payer_id' => 'int',
        'units' => 'float',
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
}