<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;
use Illuminate\Database\Eloquent\Model;

/**
 * \App\Billing\ClientInvoiceItem
 *
 * @property int $id
 * @property int $invoice_id
 * @property string|null $invoiceable_type
 * @property int $invoiceable_id
 * @property string|null $group
 * @property string $name
 * @property float $units
 * @property float $rate
 * @property float $total
 * @property float $amount_due
 * @property string|null $date
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\ClientInvoice $invoice
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|InvoiceableInterface $invoiceable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereAmountDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereInvoiceableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereInvoiceableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientInvoiceItem whereUnits($value)
 * @mixin \Eloquent
 */
class ClientInvoiceItem extends BaseInvoiceItem
{
    protected $casts = [
        'invoice_id' => 'int',
        'invoiceable_id' => 'int',
        'units' => 'float',
        'rate' => 'float',
        'total' => 'float',
        'amount_due' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function invoice()
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Get the related invoiceable Shift.
     *
     * @return Shift|null
     */
    public function getShift() : ?Shift
    {
        if ($this->invoiceable_type != 'shifts') {
            return null;
        }

        return Shift::find($this->invoiceable_id);
    }

    /**
     * Get the related invoiceable ShiftService.
     *
     * @param bool $loadShift
     * @return ShiftService|null
     */
    public function getShiftService(bool $loadShift = false) : ?ShiftService
    {
        if ($this->invoiceable_type != 'shift_services') {
            return null;
        }

        if ($loadShift) {
            return ShiftService::with('shift')->find($this->invoiceable_id);
        }
        return ShiftService::find($this->invoiceable_id);
    }
}