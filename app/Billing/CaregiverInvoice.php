<?php
namespace App\Billing;

use App\AuditableModel;

class CaregiverInvoice extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'caregiver_id' => 'int',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function items()
    {
        return $this->morphMany(InvoiceItem::class, 'invoice');
    }
}