<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceInterface;
use App\BusinessChain;
use App\Client;
use Illuminate\Database\Eloquent\Builder;

/**
 * \App\Billing\ClientInvoice
 *
 * @property int $id
 * @property string $name
 * @property int $client_id
 * @property int|null $payer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $amount
 * @property float $amount_paid
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientInvoiceItem[] $items
 * @property-read \App\Billing\Payer|null $payer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payment[] $payments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice forBusiness($businessId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice forBusinessChain(\App\BusinessChain $businessChain)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice notPaidInFull()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice paidInFull()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientInvoice extends AuditableModel implements InvoiceInterface
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'payer_id' => 'int',
    ];

    /**
     * Get the next invoice name for a client
     *
     * @param int $clientId
     * @param int $payerId
     * @return string
     */
    public static function getNextName(int $clientId, int $payerId)
    {
        return ''; // TODO
    }

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

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function getPayer(): Payer
    {
        $payer = $this->payer;
        if ($payer->isPrivatePay()) {
            $payer->setPrivatePayer($this->client);
        }
        return $payer;
    }

    function getAmount(): float
    {
        return (float) $this->amount;
    }

    function getAmountPaid(): float
    {
        return (float) $this->amount_paid;
    }

    function getAmountDue(): float
    {
        return (float) bcsub($this->getAmount(), $this->getAmountPaid(), 2);
    }

    function addItem(ClientInvoiceItem $item): bool
    {
        if ($this->items()->save($item)) {
            return $this->update(['amount' => $this->items()->sum('amount_due')]);
        }
        return false;
    }

    function addPayment(Payment $payment, float $amountApplied): bool
    {
        if ($this->payments()->save($payment, ['amount_applied' => $amountApplied])) {
            return (bool) $this->increment('amount_paid', $amountApplied);
        }
        return false;
    }

    function getName(): string
    {
        return $this->name;
    }

    function getDate(): string
    {
        return $this->created_at->format('m/d/Y');
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    function scopeForBusiness(Builder $builder, int $businessId)
    {
        $builder->whereHas('client', function($q) use ($businessId) {
            $q->where('business_id', $businessId);
        });
    }

    function scopeForBusinessChain(Builder $builder, BusinessChain $businessChain)
    {
        $builder->whereHas('client', function($q) use ($businessChain) {
            $businessIds = $businessChain->businesses()->pluck('id')->toArray();
            $q->whereIn('business_id', $businessIds);
        });
    }

    function scopePaidInFull(Builder $builder)
    {
        $builder->whereColumn('amount_paid', '==', 'amount');
    }

    function scopeNotPaidInFull(Builder $builder)
    {
        $builder->whereColumn('amount_paid', '!=', 'amount');
    }
}