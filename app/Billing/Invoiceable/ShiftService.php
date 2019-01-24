<?php
namespace App\Billing\Invoiceable;

use App\AuditableModel;
use App\Billing\Service;
use App\Business;
use App\Caregiver;
use App\Billing\Payer;
use App\Client;
use App\Shift;
use Illuminate\Support\Collection;

/**
 * \App\Billing\Invoiceable\ShiftService
 *
 * @property int $id
 * @property int $shift_id
 * @property int $service_id
 * @property int|null $payer_id
 * @property string $hours_type
 * @property float $duration
 * @property float|null $client_rate
 * @property float|null $caregiver_rate
 * @property float|null $ally_rate
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\BaseInvoiceItem[] $invoiceItems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Invoiceable\InvoiceableMeta[] $meta
 * @property-read \App\Billing\Payer|null $payer
 * @property-read \App\Billing\Service $service
 * @property-read \App\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereAllyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereClientRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableModel whereMeta($key, $delimiter = null, $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableModel withMeta()
 * @mixin \Eloquent
 */
class ShiftService extends InvoiceableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'payer_id' => 'int',
        'shift_id' => 'int',
        'service_id' => 'int',
        'client_rate' => 'float',
        'caregiver_rate' => 'float',
        'ally_rate' => 'float',
        'duration' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
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
        return self::whereHas('shift', function($query) {
            $query->where('status', Shift::WAITING_FOR_INVOICE);
        })->get();
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
        return $this->duration;
    }

    /**
     * Get the name of this item to display on the invoice
     *
     * @param string $invoiceModel
     * @return string
     */
    public function getItemName(string $invoiceModel): string
    {
        return $this->service->code . ' ' . $this->service->name;
    }

    /**
     * Get the group this item should be listed under on the invoice
     *
     * @param string $invoiceModel
     * @return string|null
     */
    public function getItemGroup(string $invoiceModel): ?string
    {
        return $this->shift->getItemGroup($invoiceModel);
    }

    /**
     * Get the date & time that this item's "service" occurred.   SHOULD respect the client/business timezone.
     * Note: This is used for sorting items on the invoice and determining payer allowances.
     *
     * @return string|null
     */
    public function getItemDate(): ?string
    {
        return $this->shift->getItemDate();
    }

    /**
     * @return string|null
     */
    public function getItemNotes(): ?string
    {
        return null;
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
     * @param float $amount
     */
    public function addAmountInvoiced(float $amount): void
    {
        // Check if all services have been invoiced
        foreach ($this->shift->services as $service) {
            if ($service->getAmountDue() > 0) {
                return;
            }
        }

        $this->shift->statusManager()->ackClientInvoice();
    }
}