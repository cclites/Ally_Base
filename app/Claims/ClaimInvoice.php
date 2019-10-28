<?php

namespace App\Claims;

use App\Billing\ClientInvoiceItem;
use App\Billing\ClientPayer;
use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Transmitters\HhaClaimTransmitter;
use App\Claims\Transmitters\ManualClaimTransmitter;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use App\Billing\ClientInvoice;
use App\Billing\ClaimService;
use App\Billing\ClaimStatus;
use App\AuditableModel;
use App\Billing\Payer;
use Carbon\Carbon;
use App\Business;
use App\Client;
use Illuminate\Support\Collection;

/**
 * App\Claims\ClaimInvoice
 *
 * @property int $id
 * @property int $business_id
 * @property int $client_invoice_id
 * @property string $name
 * @property float $amount
 * @property float $amount_due
 * @property string $status
 * @property string|null $transmission_method
 * @property int $client_id
 * @property string $client_first_name
 * @property string $client_last_name
 * @property string|null $client_dob
 * @property string|null $client_medicaid_id
 * @property string|null $client_medicaid_diagnosis_codes
 * @property int $payer_id
 * @property string $payer_name
 * @property string|null $payer_code
 * @property string|null $plan_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $modified_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Client $client
 * @property-read \App\Billing\ClientInvoice $clientInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Claims\ClaimInvoiceItem[] $items
 * @property-read \App\Billing\Payer $payer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClaimPayment[] $payments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forClient($clientId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forDateRange($start, $end)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forPayer($payerId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice hasBalance()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice query()
 * @mixin \Eloquent
 */
class ClaimInvoice extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the Business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the ClientInvoice relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clientInvoice()
    {
        return $this->belongsTo(ClientInvoice::class);
    }

    /**
     * Get the ClaimInvoiceItems relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(ClaimInvoiceItem::class, 'claim_invoice_id', 'id');
    }

    /**
     * Get the Client relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the Payer relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    /**
     * Get the ClaimAdjustments relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adjustments()
    {
        return $this->hasMany(ClaimAdjustment::class);
    }

    /**
     * Get the status relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(ClaimInvoiceStatusHistory::class);
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * @return \Illuminate\Support\Collection|\App\Claims\ClaimInvoiceItem[]
     */
    function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Get the claim name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the date of the claim.
     *
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->created_at;
    }

    /**
     * Get the total amount of the claim.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return floatval($this->amount);
    }

    /**
     * Get the claim balance..
     *
     * @return float
     */
    public function getAmountDue(): float
    {
        return floatval($this->amount_due);
    }

    /**
     * Get the total amount paid/adjusted.
     *
     * @return float
     */
    public function getAmountPaid(): float
    {
        return subtract(floatval($this->amount), floatval($this->amount_due));
    }

    /**
     * Get the ClaimStatus.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get whether or not the claim invoice has been transmitted.
     *
     * @return bool
     */
    public function hasBeenTransmitted(): bool
    {
        return ! in_array($this->status, ClaimStatus::notTransmittedStatuses());
    }

    /**
     * Get the timezone for the Claim shift data.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->business->getTimezone();
    }

    /**
     * Check if the amount of the claim is different
     * than the amount of the client invoice.
     *
     * @return bool
     */
    public function hasAmountMismatch(): bool
    {
        return $this->amount != $this->clientInvoice->amount;
    }

    /**
     * Check if the Claim has any expense items.
     *
     * @return bool
     */
    public function getHasExpenses(): bool
    {
        return $this->items->filter(function ($item) {
            /** @var ClaimInvoiceItem $item */
            return $item->claimable_type == ClaimableExpense::class;
        })->count() > 0;
    }

    /**
     * Get the ClientPayer record from the current
     * client/payer combo.
     *
     * WARNING: This has the potential to return null if the
     * Client's payer list has been modified to remove this payer.
     *
     * @return ClientPayer
     */
    public function getClientPayer() : ?ClientPayer
    {
        return $this->client->payers()
            ->where('payer_id', $this->payer_id)
            ->first();
    }


    /**
     * Get the total number of hours on the client invoice by
     * adding all the 'units' for shift related items.
     *
     * @return float
     */
    public function getTotalHours() : float
    {
        return $this->items->reduce(function (float $carry, ClaimInvoiceItem $item) {
            if ($item->claimable_type == ClaimableService::class) {
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
        return $this->items->reduce(function (float $carry, ClaimInvoiceItem $item) {
            if ($item->claimable_type == ClaimableService::class) {
                return add($carry, floatval($item->amount));
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
        return optional(optional($ordered->first())->date)->format('m/d/Y')
            . ' - ' .
            optional(optional($ordered->last())->date)->format('m/d/Y');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Filter by payer_id (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|int|string $payerId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForPayer($query, $payerId = null)
    {
        if (is_null($payerId)) {
            return $query;
        }

        return $query->where('payer_id', $payerId);
    }

    /**
     * Filter by client_id (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|int|string $clientId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForClient($query, $clientId = null)
    {
        if (empty($clientId)) {
            return $query;
        }

        return $query->where('client_id', $clientId);
    }

    /**
     * Filter by client invoiced at between the given date range.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereInvoicedBetween($query, $start, $end)
    {
        return $query->whereHas('clientInvoice', function ($q) use ($start, $end) {
            return $q->whereBetween('created_at', [$start, $end]);
        });
    }

    /**
     * Filter by dates of service between the given date range.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereDatesOfServiceBetween($query, $start, $end)
    {
        return $query->whereHas('items', function ($q) use ($start, $end) {
            return $q->whereBetween('date', [$start, $end]);
        });
    }

    /**
     * Filter to only Claims that have a balance.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeHasBalance($query)
    {
        return $query->where('amount_due', '<>', '0');
    }

    /**
     * Filter claims by client type.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForClientType($query, $clientType)
    {
        if (empty($clientType)) {
            return $query;
        }

        return $query->whereHas('client', function ($q) use ($clientType) {
            $q->where('client_type', $clientType);
        });
    }

    /**
     * Filter claims by active users.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForActiveClientsOnly($query)
    {
        return $query->whereHas('client', function ($q) {
            $q->active();
        });
    }

    /**
     * Search claims for the matching client invoice ID or Name.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param $invoiceIdOrName
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchForInvoiceId($query, $invoiceIdOrName)
    {
        if (empty($invoiceIdOrName)) {
            return $query;
        }

        return $query->whereHas('clientInvoice', function ($q) use ($invoiceIdOrName) {
            $q->where('id', $invoiceIdOrName)
                ->orWhere('name', $invoiceIdOrName);
        });
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Update the amount and amount due from the ClaimInvoiceItem values
     *
     */
    public function updateBalance(): void
    {
        $items = $this->fresh()->items;

        $amount = $items->reduce(function (float $carry, ClaimInvoiceItem $item) {
            return add($carry, (float)$item->amount);
        }, (float)0.00);

        $amount_due = $items->reduce(function (float $carry, ClaimInvoiceItem $item) {
            return add($carry, (float)$item->amount_due);
        }, (float)0.00);

        $this->update(compact('amount', 'amount_due'));
    }

    /**
     * Mark that the Claim has been modified.
     *
     * @return void
     */
    public function markAsModified(): void
    {
        $this->update(['modified_at' => Carbon::now()]);
    }

    /**
     * Set the status of the claim, and add to it's status history.
     *
     * @param \App\Billing\ClaimStatus $status
     * @param array $otherUpdates
     */
    public function updateStatus(ClaimStatus $status, array $otherUpdates = []): void
    {
        $this->update(array_merge(['status' => $status], $otherUpdates));
        $this->statuses()->create(['status' => $status]);
    }

    /**
     * Get the transmission method that should be used
     * for the ClaimInvoice.
     *
     * @return null|ClaimService
     */
    public function getTransmissionMethod(): ?ClaimService
    {
        if (empty($this->transmission_method)) {
            return null;
        }

        return ClaimService::fromValue($this->transmission_method);
    }

    /**
     * Get the ClaimTransmitter for the given service.
     *
     * @param ClaimService $service
     * @return ClaimTransmitterInterface
     * @throws ClaimTransmissionException
     */
    public function getTransmitter(ClaimService $service): ClaimTransmitterInterface
    {
        switch ($service) {
            case ClaimService::HHA():
                return new HhaClaimTransmitter();
            case ClaimService::TELLUS():
                throw new ClaimTransmissionException('Claim service "Tellus" not yet supported.');
//                return new TellusClaimTransmitter();
                break;
            case ClaimService::CLEARINGHOUSE():
                throw new ClaimTransmissionException('Claim service not yet supported.');
                break;
            case ClaimService::DIRECT_MAIL():
            case ClaimService::FAX():
            case ClaimService::EMAIL():
                return new ManualClaimTransmitter();
                break;
            default:
                throw new ClaimTransmissionException('Claim service not supported.');
        }
    }

    // **********************************************************
    // STATIC METHODS
    // **********************************************************

    /**
     * Get the next invoice name for a client
     *
     * @param int $businessId
     * @return string
     */
    public static function getNextName(int $businessId): string
    {
        $lastName = self::where('business_id', $businessId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->value('name');

        $minId = 1000;
        if (! $lastName) {
            $nextId = $minId;
        } else {
            $nextId = (int)substr($lastName, strpos($lastName, '-') + 1) + 1;
        }

        if ($nextId < $minId) {
            $nextId = $minId;
        }

        return "${businessId}-${nextId}";
    }
}
