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
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'client_id' => 'int',
        'service_id' => 'int',
        'payer_id' => 'int',
        'units' => 'float',
    ];

    // **********************************************************
    // Periods
    // **********************************************************
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';

    // **********************************************************
    // Unit Types
    // **********************************************************
    const UNIT_TYPE_HOURLY = 'hourly';
    const UNIT_TYPE_FIXED = 'fixed';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the client relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the payer relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    /**
     * Get the service relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Get only the service authorizations effective during the
     * given date.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeEffectiveOn($query, \Carbon\Carbon $date)
    {
        return $query->where('effective_start', '<=', $date->toDateString())
            ->where('effective_end', '>=', $date->toDateString());
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Get an array containing the start and end dates of the
     * authorization period.
     *
     * @return array|null
     */
    public function getPeriodDates() : ?array
    {
        switch ($this->period) {
            case self::PERIOD_DAILY:
                return [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
                break;
            case self::PERIOD_WEEKLY:
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
                break;
            case self::PERIOD_MONTHLY:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
                break;
            default:
                return null;
        }
    }
}
