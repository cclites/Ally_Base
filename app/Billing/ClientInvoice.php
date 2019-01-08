<?php
namespace App\Billing;

use App\AuditableModel;

class ClientInvoice extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'payer_id' => 'int',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function items()
    {
        return $this->morphMany(InvoiceItem::class, 'invoice');
    }
}