<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * \App\Billing\InvoiceItem
 *
*/
abstract class BaseInvoiceItem extends AuditableModel
{
    public $timestamps = false;
    protected $guarded = ['id'];
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

    function invoiceable()
    {
        return $this->morphTo();
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function associateInvoiceable(InvoiceableInterface $invoiceable, $save = false)
    {
        if ($invoiceable instanceof Model) {
            $this->invoiceable()->associate($invoiceable);
        }
        if ($save) $this->save();
    }
}