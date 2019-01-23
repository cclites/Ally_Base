<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * App\Billing\BusinessInvoice
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\BusinessInvoiceItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
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
        return $this->hasMany(BusinessInvoiceItem::class, 'invoice');
    }

    function deposits()
    {
        return $this->morphToMany(Deposit::class, 'invoice', 'invoice_deposits');
    }
}