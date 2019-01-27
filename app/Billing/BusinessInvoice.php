<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * \App\Billing\BusinessInvoice
 *
 * @property int $id
 * @property string $name
 * @property int $business_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float $amount
 * @property float $amount_paid
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\BusinessInvoiceItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice whereUpdatedAt($value)
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
        return $this->hasMany(BusinessInvoiceItem::class, 'invoice_id');
    }

    function deposits()
    {
        return $this->morphToMany(Deposit::class, 'invoice', 'invoice_deposits')
            ->withPivot(['amount_applied']);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function addItem(BusinessInvoiceItem $item): bool
    {
        if ($this->items()->save($item)) {
            return $this->update(['amount' => $this->items()->sum('total')]);
        }
        return false;
    }

    function addDeposit(Deposit $deposit, float $amountApplied): bool
    {
        if ($this->deposits()->save($deposit, ['amount_applied' => $amountApplied])) {
            return (bool) $this->increment('amount_paid', $amountApplied);
        }
        return false;
    }
}