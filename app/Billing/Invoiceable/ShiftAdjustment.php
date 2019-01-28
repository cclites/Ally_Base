<?php
namespace App\Billing\Invoiceable;

use App\Billing\ClientInvoiceItem;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Shift;
use Illuminate\Support\Collection;

/**
 * \App\Billing\Invoiceable\ShiftAdjustment
 *
 * @property int $id
 * @property int $business_id
 * @property int $client_id
 * @property int $caregiver_id
 * @property int $payer_id
 * @property int $service_id
 * @property int $shift_id
 * @property float $units
 * @property float $client_rate
 * @property float $caregiver_rate
 * @property float $ally_rate
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\BaseInvoiceItem[] $invoiceItems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Invoiceable\InvoiceableMeta[] $meta
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereAllyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereClientRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableModel whereMeta($key, $delimiter = null, $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftAdjustment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableModel withMeta()
 * @mixin \Eloquent
 */
class ShiftAdjustment extends InvoiceableModel
{
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
     * @return \Illuminate\Support\Collection                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForPayment(Client $client): Collection
    {
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
        return $this->notes;
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
     * TODO Implement caregiver deposit invoicing
     * @return float
     */
    public function getCaregiverRate(): float
    {
        return $this->caregiver_rate;
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

    /**
     * TODO Implement business deposit invoicing
     * Note: This is a calculated field from the other rates
     * @return float
     */
    public function getProviderRate(): float
    {
        // TODO: Implement getProviderRate() method.
    }

    /**
     * Get the assigned payer ID (payers.id, not client_payers.id)
     *
     * @return int|null
     */
    public function getPayerId(): ?int
    {
        return $this->payer_id;
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
}