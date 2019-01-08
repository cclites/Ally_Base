<?php
namespace App\Billing;

use App\AuditableModel;
use App\Client;

class ClientPayer extends AuditableModel
{
    protected $orderedColumn = 'priority';
    protected $guarded = ['id'];
    protected $with = ['payer'];

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
}