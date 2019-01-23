<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Database\Eloquent\Model;


class BusinessInvoiceItem extends BaseInvoiceItem
{
    protected $casts = [
        'invoice_id' => 'int',
        'invoiceable_id' => 'int',
        'units' => 'float',
        'client_rate' => 'float',
        'caregiver_rate' => 'float',
        'ally_rate' => 'float',
        'rate' => 'float',
        'total' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function invoice()
    {
        return $this->belongsTo(BusinessInvoice::class, 'invoice_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////
}