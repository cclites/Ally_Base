<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * App\Billing\CaregiverInvoice
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\CaregiverInvoiceItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
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
        return $this->morphMany(CaregiverInvoiceItem::class, 'invoice');
    }

    function deposits()
    {
        return $this->morphToMany(Deposit::class, 'invoice', 'invoice_deposits');
    }
}