<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * \App\Billing\ClientInvoice
 *
 * @property int $id
 * @property string $name
 * @property int $client_id
 * @property int $payer_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientInvoiceItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientInvoice extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'payer_id' => 'int',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function items()
    {
        return $this->hasMany(ClientInvoiceItem::class, 'invoice_id');
    }

    function payments()
    {
        return $this->belongsToMany(Payment::class, 'invoice_payments', 'invoice_id', 'payment_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function getAmountDue(): float
    {
        return (float) $this->items()->sum('amount_due');
    }

    function addItem(ClientInvoiceItem $item): bool
    {
        if ($this->items()->save($item)) {
            return $this->update(['amount' => $this->items()->sum('amount_due')]);
        }
        return false;
    }
}