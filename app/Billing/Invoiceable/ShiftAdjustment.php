<?php
namespace App\Billing\Invoiceable;

use App\Billing\ClientInvoiceItem;
use App\Billing\Payer;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Shift;
use App\Traits\BelongsToOneBusiness;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * \App\Billing\Invoiceable\ShiftAdjustment
 *
 * @property int $id
 * @property int $business_id
 * @property int $client_id
 * @property int $caregiver_id
 * @property int|null $payer_id
 * @property int|null $service_id
 * @property int|null $shift_id
 * @property float $units
 * @property float $client_rate
 * @property float $caregiver_rate
 * @property float|null $ally_rate
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientInvoiceItem[] $clientInvoiceItems
 * @property-read \App\Billing\ClientPayer $clientPayer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Invoiceable\InvoiceableMeta[] $meta
 * @property-read \App\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableModel whereMeta($key, $delimiter = null, $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableModel withMeta()
 * @mixin \Eloquent
 */
class ShiftAdjustment extends InvoiceableModel
{
    use BelongsToOneBusiness;

    protected $table = 'shift_adjustments';
    protected $guarded = ['id'];
    protected $casts = [
        'business_id' => 'int',
        'client_id' => 'int',
        'caregiver_id' => 'int',
        'payer_id' => 'int',
        'service_id' => 'int',
        'shift_id' => 'int',
        'units' => 'float',
        'client_rate' => 'float',
        'caregiver_rate' => 'float',
        'ally_rate' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////


    /**
     * Collect all applicable invoiceables of this type eligible for the client payment
     *
     * @param \App\Client $client
     * @param \Carbon\Carbon $endDateUtc
     * @return \Illuminate\Support\Collection                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForPayment(Client $client, Carbon $endDateUtc): Collection
    {
        // TODO: Implement getItemsForPayment() method.
        // This should probably use the InvoiceableQuery class, like Shift does
        return self::where('client_id', $client->id)
            ->where('status', Shift::WAITING_FOR_INVOICE)
            ->get();
    }

    /**
     * Collect all applicable invoiceables of this type eligible for the caregiver deposit
     *
     * @param \App\Caregiver $caregiver
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForCaregiverDeposit(Caregiver $caregiver): Collection
    {
        // TODO: Implement getItemsForCaregiverDeposit() method.
        return new Collection();
    }

    /**
     * Collect all applicable invoiceables of this type eligible for the provider deposit
     *
     * @param \App\Business $business
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForBusinessDeposit(Business $business): Collection
    {
        // TODO: Implement getItemsForBusinessDeposit() method.
        return new Collection();
    }

    /**
     * Get the number of units to be invoiced
     *
     * @return float
     */
    public function getItemUnits(): float
    {
        return $this->units;
    }

    /**
     * Get the name of this item to display on the invoice
     *
     * @param string $invoiceModel
     * @return string
     */
    public function getItemName(string $invoiceModel): string
    {
        return 'Shift Adjustment';
    }

    /**
     * Get the group this item should be listed under on the invoice
     *
     * @param string $invoiceModel
     * @return string|null
     */
    public function getItemGroup(string $invoiceModel): ?string
    {
        return 'Adjustments';
    }

    /**
     * Get the date & time that this item's "service" occurred.   SHOULD respect the client/business timezone.
     * Note: This is used for sorting items on the invoice and determining payer allowances.
     *
     * @return string|null
     */
    public function getItemDate(): ?string
    {
        return $this->shift ? $this->shift->getItemDate() : $this->created_at;
    }

    /**
     * @return string|null
     */
    public function getItemNotes(): ?string
    {
        return str_limit($this->notes, 252);
    }

    /**
     * Check if the client rate includes the ally fee (ex. true for shifts, false for expenses)
     *
     * @return bool
     */
    public function hasFeeIncluded(): bool
    {
        return true;
    }

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function getClient(): ?Client
    {
        return $this->shift->getClient();
    }

    /**
     * Get the client rate of this item (payment rate).  The total charged will be this rate multiplied by the units.
     *
     * @return float
     */
    public function getClientRate(): float
    {
        return $this->client_rate;
    }

    /**
     * @return float
     */
    public function getCaregiverRate(): float
    {
        return $this->caregiver_rate;
    }

    public function getCaregiver(): ?Caregiver
    {
        return $this->shift->getCaregiver();
    }

    /**
     * Return the ally fee per unit for this invoiceable item.  If this returns null, abort invoicing this item.  Return 0.0 for no ally fee.
     *
     * @return float|null
     */
    public function getAllyRate(): ?float
    {
        // TODO: Implement getAllyRate() method.
    }

    public function getBusiness(): ?Business
    {
        return $this->shift->getBusiness();
    }

    /**
     * TODO Implement business deposit invoicing
     * Note: This is a calculated field from the other rates
     * @return float
     */
    public function getProviderRate(): float
    {
        // TODO: Implement getProviderRate() method.
        return floatval(0);
    }

    /**
     * Add an amount that has been invoiced to a payer
     *
     * @param \App\Billing\ClientInvoiceItem $invoiceItem
     * @param float $amount
     * @param float $allyFee  The value of $amount that represents the Ally Fee
     */
    public function addAmountInvoiced(ClientInvoiceItem $invoiceItem, float $amount, float $allyFee): void
    {
        // TODO: Implement addAmountInvoiced() method.
    }

    /**
     * A query scope for filtering invoicables by related caregiver IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $caregiverIds
     * @return void
     */
    public function scopeForCaregivers(Builder $builder, array $caregiverIds)
    {
        $builder->whereIn('caregiver_id', $caregiverIds);
    }
}