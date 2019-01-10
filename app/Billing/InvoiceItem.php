<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * App\Billing\InvoiceItem
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class InvoiceItem extends AuditableModel
{
    protected $guarded = ['id', 'amount_invoiced'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function invoice()
    {
        return $this->morphTo();
    }

    function invoiceable()
    {
        return $this->morphTo();
    }
}