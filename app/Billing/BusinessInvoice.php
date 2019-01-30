<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Business;
use Illuminate\Support\Collection;

/**
 * \App\Billing\BusinessInvoice
 *
 * @property int $id
 * @property string $name
 * @property int $business_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $amount
 * @property float $amount_paid
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\BusinessInvoiceItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoice query()
 * @mixin \Eloquent
 */
class BusinessInvoice extends AuditableModel implements DepositInvoiceInterface
{
    protected $guarded = ['id'];

    protected $casts = [
        'business_id' => 'int',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function business()
    {
        return $this->belongsTo(Business::class);
    }

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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getAmountDue(): float
    {
        return subtract($this->amount, $this->amount_paid);
    }

    public function getItems(): Collection
    {
        return $this->items;
    }
}