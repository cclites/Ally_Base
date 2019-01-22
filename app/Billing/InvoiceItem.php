<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * \App\Billing\InvoiceItem
 *
 * @property int $id
 * @property string $invoice_type
 * @property int $invoice_id
 * @property string $invoiceable_type
 * @property int $invoiceable_id
 * @property string|null $group
 * @property string $name
 * @property float $units
 * @property float $rate
 * @property float $total
 * @property float $amount_due
 * @property string|null $date
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $invoice
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $invoiceable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereAmountDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereInvoiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereInvoiceableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereInvoiceableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\InvoiceItem whereUnits($value)
 * @mixin \Eloquent
 */
class InvoiceItem extends AuditableModel
{
    public $timestamps = false;
    protected $guarded = ['id', 'amount_invoiced'];
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
        return $this->morphTo();
    }

    function invoiceable()
    {
        return $this->morphTo();
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function associateInvoiceable(InvoiceableInterface $invoiceable, $save = false)
    {
        if ($invoiceable instanceof Model) {
            $this->invoiceable()->associate($invoiceable);
        }
        if ($save) $this->save();
    }
}