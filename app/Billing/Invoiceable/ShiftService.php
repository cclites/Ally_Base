<?php
namespace App\Billing\Invoiceable;

use App\Billing\ClientInvoiceItem;
use App\Billing\Invoiceable\Traits\BelongsToThroughShift;
use App\Billing\Service;
use App\Billing\Payer;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Shift;
use Carbon\Carbon;
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
 * @property int $quickbooks_service_id
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
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClientInvoiceItem[] $clientInvoiceItems
 * @property-read int|null $client_invoice_items_count
 * @property-read int|null $meta_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService forCaregivers($caregiverIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\ShiftService query()
 */
class ShiftService extends InvoiceableModel
{
    use BelongsToThroughShift;

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
     * @param \Carbon\Carbon $endDateUtc
     * @return \Illuminate\Support\Collection
     */
    public function getItemsForPayment(Client $client, Carbon $endDateUtc): Collection
    {
        return new Collection([]); // Handled by App\Shift
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

    public function getBusiness(): ?Business
    {
        return $this->shift->getBusiness();
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
        // Check if all services have been invoiced
        foreach ($this->shift->services as $service) {
            if ($service->getAmountDue() > 0) {
                return;
            }
        }

        $this->shift->statusManager()->ackClientInvoice();
    }

    /**
     * Get the start and end time of this service based on
     * it's duration.
     *
     * @return array
     */
    public function getStartAndEndTime() : array
    {
        // The only way to do this is to enumerate every service on the shift
        // in order and add the duration to the start time of the shift.
        $start = $this->shift->checked_in_time->copy();
        $end = null;

        /** @var ShiftService $service */
        foreach ($this->shift->services as $service) {
            $end = $start->copy()->addMinutes(($service->duration * 60));

            if ($service->id === $this->id) {
                // Dump the start/end time if this is the current service.
                return [$start, $end];
            }

            $start = $end->copy()->addSecond(1);
        }

        return [$this->shift->checked_in_time, $this->shift->checked_out_time];
    }
}