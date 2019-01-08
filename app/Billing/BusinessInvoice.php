<?php
namespace App\Billing;

use App\AuditableModel;

class BusinessInvoice extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'business_id' => 'int',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function items()
    {
        return $this->morphMany(InvoiceItem::class, 'invoice');
    }
}