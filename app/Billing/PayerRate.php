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