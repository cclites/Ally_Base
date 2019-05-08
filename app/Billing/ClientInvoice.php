<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceInterface;
use App\Client;
use Illuminate\Support\Collection;

/**
 * \App\Billing\ClientInvoice
 *
 * @property int $id
 * @property string $name
 * @property int $client_id
 * @property int|null $client_payer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $amount
 * @property float $amount_paid
 * @property bool $offline
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\Claim $claim
 * @property-read \App\Client $client
 * @property-read \App\Billing\ClientPayer|null $clientPayer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientInvoiceItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payment[] $payments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoice query()
 * @mixin \Eloquent
 */
class ClientInvoice extends AuditableModel implements InvoiceInterface
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'payer_id' => 'int',
        'amount' => 'float',
        'amount_paid' => 'float',
        'offline' => 'bool',
    ];

    /**
     * Get the next invoice name for a client
     *
     * @param int $clientId
     * @return string
     */
    public static function getNextName(int $clientId)
    {
        $lastName = self::where('client_id', $clientId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->value('name');


        $minId = 1000;
        if (!$lastName) {
            $nextId = $minId;
        } else {
            $nextId = (int) substr($lastName, strpos($lastName, '-') + 1) + 1;
        }

        if ($nextId < $minId) {
            $nextId = $minId;
        }

        return "${clientId}-${nextId}";
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
        return $this->belongsToMany(Payment::class, 'invoice_payments', 'invoice_id', 'payment_id')
            ->withPivot(['amount_applied']);
    }

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    function clientPayer()
    {
        return $this->belongsTo(ClientPayer::class);
    }

    public function claim()
    {
        return $this->hasOne(Claim::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function isOffline(): bool
    {
        return (bool) $this->offline;
    }

    function getClientPayer(): ?ClientPayer
    {
        return $this->clientPayer;
    }

    function getAmount(): float
    {
        return (float) $this->amount;
    }

    function getAmountPaid(): float
    {
        if ($this->isOffline()) {
            return (float) $this->offlinePayments()->sum('amount');
        }

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

    /**
     * @return \Illuminate\Support\Collection|\App\Billing\ClientInvoiceItem[]
     */
    function getItems(): Collection
    {
        return $this->items;
    }

    function getItemGroups(): Collection
    {
        return $this->getItems()->sortBy('date')->groupBy('group');
    }

    function addPayment(Payment $payment, float $amountApplied): bool
    {
        if ($this->payments()->save($payment, ['amount_applied' => $amountApplied])) {
            return (bool) $this->increment('amount_paid', $amountApplied);
        }

        return false;
    }

    function removePayment(Payment $payment): bool
    {
        if ($payment = $this->payments->where('id', $payment->id)->first()) {
            $this->payments()->syncWithoutDetaching([$payment->id => ['amount_applied' => 0]]);
            return (bool) $this->decrement('amount_paid', $payment->pivot->amount_applied);
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

    function getEstimates(): ClientInvoiceEstimates
    {
        return new ClientInvoiceEstimates($this);
    }
}