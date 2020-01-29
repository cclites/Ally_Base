<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use App\Traits\ScrubsForSeeding;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Billing\BusinessInvoiceItem
 *
 * @property int $id
 * @property int $invoice_id
 * @property string|null $invoiceable_type
 * @property int|null $invoiceable_id
 * @property string|null $group
 * @property string $name
 * @property float $units
 * @property float $client_rate
 * @property float $caregiver_rate
 * @property float $ally_rate
 * @property float $rate
 * @property float $total
 * @property string|null $date
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Billing\BusinessInvoice $invoice
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $invoiceable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\BusinessInvoiceItem query()
 * @mixin \Eloquent
 */
class BusinessInvoiceItem extends BaseInvoiceItem
{
    protected $casts = [
        'invoice_id' => 'int',
        'invoiceable_id' => 'int',
        'units' => 'float',
        'client_rate' => 'float',
        'caregiver_rate' => 'float',
        'ally_rate' => 'float',
        'rate' => 'float',
        'total' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function invoice()
    {
        return $this->belongsTo(BusinessInvoice::class, 'invoice_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

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