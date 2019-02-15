<?php
namespace App\Billing;

use App\AuditableModel;
use Carbon\Carbon;
use App\Client;

/**
 * App\Billing\ClientAuthorization
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \App\Billing\Payer $payer
 * @property-read \App\Billing\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class ClientAuthorization extends AuditableModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'client_id' => 'int',
        'service_id' => 'int',
        'payer_id' => 'int',
        'units' => 'float',
    ];

    ////////////////////////////////////
    //// Periods
    ////////////////////////////////////

    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    
    ////////////////////////////////////
    //// Unit Types
    ////////////////////////////////////

    const UNIT_TYPE_HOURLY = 'hourly';
    const UNIT_TYPE_FIXED = 'fixed';
    
    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * Get only the active serice authorizations.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('effective_start', '<=', Carbon::now()->toDateString())
            ->where('effective_end', '>=', Carbon::now()->toDateString());
    }
}