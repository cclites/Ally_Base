<?php
namespace App\Billing\Invoiceable;

use Packages\MetaData\MetaData;

/**
 * App\Billing\Invoiceable\InvoiceableMeta
 *
 * @property int $id
 * @property string $metable_type
 * @property int $metable_id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $metable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableMeta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableMeta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\Invoiceable\InvoiceableMeta query()
 * @mixin \Eloquent
 */
class InvoiceableMeta extends MetaData
{
    protected $table = 'invoiceable_meta';

}