<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceInterface;
use App\Billing\Events\InvoiceablePaymentAdded;
use App\Billing\Events\InvoiceablePaymentRemoved;
use App\Billing\Events\InvoiceableUninvoiced;
use App\Claims\ClaimInvoice;
use App\Client;
use App\QuickbooksClientInvoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    public function claimInvoices()
    {
        return $this->belongsToMany( ClaimInvoice::class );
    }

    /**
     * Get the QuickbooksClientInvoice relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function quickbooksInvoice()
    {
        return $this->hasOne(QuickbooksClientInvoice::class, 'client_invoice_id', 'id');
    }

    /**
     * Get the offline payments relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function offlinePayments()
    {
        return $this->hasMany(OfflineInvoicePayment::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function getIsPaidAttribute(): bool
    {
        return $this->getAmountPaid() == $this->getAmount();
    }

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
            return $this->getOfflineAmountPaid();
        }

        return (float) $this->amount_paid;
    }

    function getOfflineAmountPaid() : float
    {
        return (float) $this->offline_amount_paid;
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
        if ($this->payments()->save($payment, ['amount_applied' => $amountApplied])
            && $this->increment('amount_paid', $amountApplied)) {
            foreach($this->getItems() as $item) {
                if ($item->invoiceable) {
                    event(new InvoiceablePaymentAdded($item->invoiceable, $this, $payment));
                }
            }

            return true;
        }

        return false;
    }

    function removePayment(Payment $payment): bool
    {
        if (($payment = $this->payments->where('id', $payment->id)->first())
            && $this->payments()->syncWithoutDetaching([$payment->id => ['amount_applied' => 0]])
            && $this->decrement('amount_paid', $payment->pivot->amount_applied))
        {
            foreach($this->getItems() as $item) {
                if ($item->getInvoiceable()) {
                    event(new InvoiceablePaymentRemoved($item->getInvoiceable(), $this, $payment));
                }
            }
        }

        return false;
    }

    function delete()
    {
        // Collect invoiceables prior to delete
        $invoiceables = [];
        foreach($this->getItems() as $item) {
            if ($item->getInvoiceable()) {
                $invoiceables[] = $item->getInvoiceable();
            }
        }

        // Call parent delete, emitting event if successful
        if ($return = parent::delete()) {
            foreach($invoiceables as $invoiceable) {
                event(new InvoiceableUninvoiced($invoiceable));
            }
        }

        return $return;
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

    function addOfflinePayment(OfflineInvoicePayment $offlinePayment): bool
    {
        if ($this->offlinePayments()->save($offlinePayment)) {
            return (bool) $this->increment('offline_amount_paid', $offlinePayment->amount);
        }

        return false;
    }

    /**
     * Determine if the invoice was a split invoice.
     *
     * @return bool
     */
    public function getWasSplit() : bool
    {
        return ! $this->items->where('was_split', 1)->isEmpty();
    }

    /**
     * Check if the invoice has items that are partially paid
     * or might have rounding issues.
     *
     * @return bool
     */
    public function getHasPartialPayment() : bool
    {
        foreach ($this->items as $item) {
            if ($item->total <> $item->amount_due) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get plan code for claim transmissions.
     *
     * @return string|null
     */
    public function getPlanCode() : ?string
    {
        if (empty($this->clientPayer)) {
            return null;
        }

        if ($this->clientPayer->isPrivatePay()) {
            return $this->clientPayer->client->medicaid_plan_id;
        }

        return $this->clientPayer->payer->getPlanCode();
    }

    /**
     * Get payer code for claim transmissions.
     *
     * @return string|null
     */
    public function getPayerCode() : ?string
    {
        if (empty($this->clientPayer)) {
            return null;
        }

        if ($this->clientPayer->isPrivatePay()) {
            return $this->clientPayer->client->medicaid_payer_id;
        }

        return $this->clientPayer->payer->getPayerCode();
    }

    /**
     * Get the total number of hours on the client invoice by
     * adding all the 'units' for shift related items.
     *
     * @return float
     */
    public function getTotalHours() : float
    {
        return $this->items->reduce(function (float $carry, ClientInvoiceItem $item) {
            if ($item->invoiceable_type == 'shifts' || $item->invoiceable_type == 'shift_services') {
                return add($carry, floatval($item->units));
            }

            return $carry;

        }, floatval(0.00));
    }

    /**
     * Get the total number of 'hourly' charges on the invoice by
     * adding the amounts for shift related items.
     *
     * @return float
     */
    public function getTotalHourlyCharges() : float
    {
        return $this->items->reduce(function (float $carry, ClientInvoiceItem $item) {
            if ($item->invoiceable_type == 'shifts' || $item->invoiceable_type == 'shift_services') {
                return add($carry, floatval($item->total));
            }

            return $carry;

        }, floatval(0.00));
    }

    /**
     * Get the date range of the attached items.  Example:
     * If invoice contains shifts for 10/01/2019, 10/15/2019, 10/31/2019
     * This method should return [10/01/2019, 10/31/2019]
     *
     * @return string
     */
    public function getDateSpan() : string
    {
        if (empty($this->items)) {
            return [null, null];
        }

        $ordered = $this->items->sortBy('date');
        return optional(optional($ordered->first())->getDate())->format('m/d/Y')
            . ' - ' .
            optional(optional($ordered->last())->getDate())->format('m/d/Y');
    }
    
    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }
    
    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('notes');
    }

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'notes' => $faker->sentence,
        ];
    }
}