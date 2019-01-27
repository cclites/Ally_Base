<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * \App\Billing\CaregiverInvoice
 *
 * @property int $id
 * @property string $name
 * @property int $caregiver_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float $amount
 * @property float $amount_paid
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\CaregiverInvoiceItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice whereUpdatedAt($value)
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
        return $this->hasMany(CaregiverInvoiceItem::class, 'invoice_id');
    }

    function deposits()
    {
        return $this->morphToMany(Deposit::class, 'invoice', 'invoice_deposits')
            ->withPivot(['amount_applied']);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function addItem(CaregiverInvoiceItem $item): bool
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