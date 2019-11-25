<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Database\Eloquent\Model;


class CaregiverInvoiceItem extends BaseInvoiceItem
{
    protected $casts = [
        'invoice_id' => 'int',
        'invoiceable_id' => 'int',
        'units' => 'float',
        'rate' => 'float',
        'total' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function invoice()
    {
        return $this->belongsTo(CaregiverInvoice::class, 'invoice_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

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