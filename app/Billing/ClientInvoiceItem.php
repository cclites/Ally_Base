<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Invoiceable\ShiftExpense;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Get the Carbon parsed object for the item date.
     *
     * @return Carbon
     */
    public function getDate() : Carbon
    {
        return Carbon::parse($this->date);
    }

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
     * @return ShiftService|null
     */
    public function getShiftService() : ?ShiftService
    {
        if ($this->invoiceable_type != 'shift_services') {
            return null;
        }

        return ShiftService::find($this->invoiceable_id);
    }

    /**
     * Get the related Shift from the invoiceable item.
     * WARNING: This does not check invoiceable_type and can provide
     * unexpected results if you do not check invoiceable_type before
     * using this relationship.
     *
     * @return HasOne
     */
    public function shift()
    {
        return $this->hasOne(Shift::class, 'id', 'invoiceable_id');
    }

    /**
     * Get the related ShiftService from the invoiceable item.
     * WARNING: This does not check invoiceable_type and can provide
     * unexpected results if you do not check invoiceable_type before
     * using this relationship.
     *
     * @return HasOne
     */
    public function shiftService()
    {
        return $this->hasOne(ShiftService::class, 'id', 'invoiceable_id');
    }


    /**
     * Get the related ShiftExpense from the invoiceable item.
     * WARNING: This does not check invoiceable_type and can provide
     * unexpected results if you do not check invoiceable_type before
     * using this relationship.
     *
     * @return HasOne
     */
    public function shiftExpense()
    {
        return $this->hasOne(ShiftExpense::class, 'id', 'invoiceable_id');
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

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
        $data = [
            'notes' => $faker->sentence,
        ];

        if ($fast) {
            $data['group'] = $faker->dateTimeThisMonth->format('F j g:iA') . ': '.$faker->name().' - '.$faker->name();
        }
        else if (strpos($item->group, ': ') > 0) {
            // Remove names from groups
            $data['group'] = substr($item->group, 0, strpos($item->group, ': ')) . ': ' . $faker->name() . ' - ' . $faker->name();
        }

        return $data;
    }
}
