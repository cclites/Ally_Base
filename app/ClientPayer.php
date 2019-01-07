<?php
namespace App;

class ClientPayer extends AuditableModel
{
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