<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * App\Billing\PayerRate
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\Payer $payer
 * @property-read \App\Billing\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 * @property int $id
 * @property int $payer_id
 * @property int|null $service_id
 * @property string $effective_start
 * @property string $effective_end
 * @property float $hourly_rate
 * @property float $fixed_rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\PayerRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\PayerRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\PayerRate query()
 */
class PayerRate extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'payer_id' => 'int',
        'service_id' => 'int',
        'hourly_rate' => 'float',
        'fixed_rate' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
    }
}