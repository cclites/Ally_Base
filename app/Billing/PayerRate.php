<?php
namespace App\Billing;

use App\AuditableModel;

class PayerRate extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'payer_id' => 'int',
        'service_id' => 'int',
        'hourly_rate' => 'float',
        'fixed_rate' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
    }
}