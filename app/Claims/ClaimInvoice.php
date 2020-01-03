<?php

namespace App\Claims;

use App\ClaimInvoiceTellusFile;
use App\Claims\Contracts\TransmissionFileInterface;
use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Transmitters\HhaClaimTransmitter;
use App\Claims\Transmitters\ManualClaimTransmitter;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Claims\Transmitters\TellusClaimTransmitter;
use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Support\Collection;
use App\Billing\ClientInvoice;
use App\Billing\ClaimService;
use App\Billing\ClaimStatus;
use App\AuditableModel;
use App\Billing\Payer;
use Carbon\Carbon;
use App\Business;
use App\Client;

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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['modified_at'];

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
     * Get the client invoices relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clientInvoices()
    {
        return $this->belongsToMany(ClientInvoice::class);
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
     * Get the ClaimInvoiceItems that are ClaimableServices.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serviceItems()
    {
        return $this->hasMany(ClaimInvoiceItem::class, 'claim_invoice_id', 'id')
            ->where('claimable_type', ClaimableService::class);
    }

    /**
     * Get the ClaimInvoiceItems that are ClaimableExpenses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenseItems()
    {
        return $this->hasMany(ClaimInvoiceItem::class, 'claim_invoice_id', 'id')
            ->where('claimable_type', ClaimableExpense::class);
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

    /**
     * Get the HhaFiles relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hhaFiles()
    {
        return $this->hasMany(ClaimInvoiceHhaFile::class, 'claim_invoice_id', 'id');
    }

    /**
     * Get the TellusFiles relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tellusFiles()
    {
        return $this->hasMany(ClaimInvoiceTellusFile::class, 'claim_invoice_id', 'id');
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
        return !in_array($this->status, ClaimStatus::notTransmittedStatuses());
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
        return $this->amount != $this->getTotalInvoicedAmount();
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
     * Get the total number of hours on the client invoice by
     * adding all the 'units' for shift related items.
     *
     * @param int|null $clientInvoiceId
     * @return float
     */
    public function getTotalHours(?int $clientInvoiceId = null): float
    {
        return $this->items->reduce(function (float $carry, ClaimInvoiceItem $item) use ($clientInvoiceId) {
            if ($item->client_invoice_id == $clientInvoiceId && $item->claimable_type == ClaimableService::class) {
                return add($carry, floatval($item->units));
            }

            return $carry;

        }, floatval(0.00));
    }

    /**
     * Get the total number of 'hourly' charges on the invoice by
     * adding the amounts for shift related items.
     *
     * @param int|null $clientInvoiceId
     * @return float
     */
    public function getTotalHourlyCharges(?int $clientInvoiceId = null): float
    {
        return $this->items->reduce(function (float $carry, ClaimInvoiceItem $item) use ($clientInvoiceId) {
            if ($item->client_invoice_id == $clientInvoiceId && $item->claimable_type == ClaimableService::class) {
                return add($carry, floatval($item->amount));
            }

            return $carry;

        }, floatval(0.00));
    }

    /**
     * Get the total amount of the claim.
     *
     * @param int $clientInvoiceId
     * @return float
     */
    public function getAmountForInvoice(int $clientInvoiceId): float
    {
        return $this->items->reduce(function (float $carry, ClaimInvoiceItem $item) use ($clientInvoiceId) {
            if ($item->client_invoice_id == $clientInvoiceId) {
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
    public function getDateSpan(): string
    {
        if (empty($this->items)) {
            return [null, null];
        }

        $ordered = $this->items->sortBy('date');
        return optional(optional($ordered->first())->date)->format('m/d/Y')
            . ' - ' .
            optional(optional($ordered->last())->date)->format('m/d/Y');
    }

    /**
     * Get the related client model for the claim invoice
     * if the claim does not represent multiple Clients.
     *
     * @return Client|null
     */
    public function getSingleClient(): ?Client
    {
        if ($this->clientInvoices->unique('client_id')->values()->count() == 1) {
            return optional($this->clientInvoices[0])->client;
        }

        return null;
    }

    /**
     * Get the total amount of all the attached client invoices.
     *
     * @return float
     */
    public function getTotalInvoicedAmount(): float
    {
        return floatval($this->clientInvoices->sum('amount'));
    }

    /**
     * Check whether or not the claim has multiple client invoices attached.
     *
     * @return bool
     */
    public function hasMultipleInvoices(): bool
    {
        return $this->clientInvoices->count() > 1;
    }

    /**
     * Attempt to get a single value for the client medicaid id
     * from the first service on the claim has that one filled out.
     *
     * @return string|null
     */
    public function getClientMedicaidId(): ?string
    {
        return $this->items->map(function ($item) {
            return $item->client_medicaid_id;
        })
            ->filter()
            ->first();
    }

    /**
     * Get first instance of a the given field from the items.
     *
     * @param string $field
     * @return string|null
     */
    public function getFirstItemData(string $field): ?string
    {
        return $this->items->map(function ($item) use ($field) {
            return $item->$field;
        })
            ->filter()
            ->first();
    }

    /**
     * Get the extra data that should be printed on claim invoices.
     *
     * @return array
     */
    public function getInvoiceClientData(): array
    {
        if ($this->claim_invoice_type == ClaimInvoiceType::PAYER()) {
            return [];
        }

        $data = collect([]);

        if ($value = $this->getFirstItemData('client_dob')) {
            $data->push('DOB: ' . Carbon::parse($value)->format('m/d/Y'));
        }

        if ($value = $this->getFirstItemData('client_ltci_claim_number')) {
            $data->push("Claim #: $value");
        }

        if ($value = $this->getFirstItemData('client_ltci_policy_number')) {
            $data->push("Policy Number #: $value");
        }

        if ($value = $this->getFirstItemData('client_hic')) {
            $data->push("HIC: $value");
        }

        if ($value = $this->getFirstItemData('services_coordinator')) {
            $data->push("Srvcs Coord: $value");
        }

        return $data->toArray();
    }

    /**
     * Get the extra data that should be printed on claim invoices.
     *
     * @return array
     */
    public function getInvoiceNotesData(): array
    {
        if ($this->claim_invoice_type == ClaimInvoiceType::PAYER()) {
            return [];
        }

        $data = collect([]);

        if ($value = $this->getFirstItemData('client_invoice_notes')) {
            $data->push($value);
        }

        if ($value = $this->getFirstItemData('client_cirts_number')) {
            $data->push("CIRTS ID:: $value");
        }

        if ($value = $this->getFirstItemData('client_program_number')) {
            $data->push("Program ID: $value");
        }

        return $data->toArray();
    }

    /**
     * Get the file record of the last claim transmission, along with
     * it's results.
     *
     * @return TransmissionFileInterface|null
     */
    public function getLatestTransmissionFile() : ?TransmissionFileInterface
    {
        switch ($this->transmission_method) {
            case ClaimService::TELLUS():
                return $this->tellusFiles()
                    ->with('results')
                    ->latest()
                    ->first();

            case ClaimService::HHA():
                return $this->hhaFiles()
                    ->with('results')
                    ->latest()
                    ->first();

            default:
                return null;
        }
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

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
                return new TellusClaimTransmitter();
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

    /**
     * Get the ClaimInvoiceType.
     *
     * @return ClaimInvoiceType
     */
    public function getType(): ClaimInvoiceType
    {
        return ClaimInvoiceType::fromValue($this->claim_invoice_type);
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
        if (!$lastName) {
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
