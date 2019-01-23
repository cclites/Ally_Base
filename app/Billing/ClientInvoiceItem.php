<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Database\Eloquent\Model;

class ClientInvoiceItem extends BaseInvoiceItem
{
    protected $casts = [
        'invoice_id' => 'int',
        'invoiceable_id' => 'int',
        'units' => 'float',
        'rate' => 'float',
        'total' => 'float',
        'amount_due' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function invoice()
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////
}