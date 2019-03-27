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
        'sunday' => 'float',
        'monday' => 'float',
        'tuesday' => 'float',
        'wednesday' => 'float',
        'thursday' => 'float',
        'friday' => 'float',
        'saturday' => 'float',
    ];

    // **********************************************************
    // Periods
    // **********************************************************
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_TERM = 'term';
    const PERIOD_SPECIFIC_DAYS = 'specific_days';

    public static function allPeriods()
    {
        return [self::PERIOD_SPECIFIC_DAYS, self::PERIOD_TERM, self::PERIOD_DAILY, self::PERIOD_MONTHLY, self::PERIOD_WEEKLY];
    }

    // **********************************************************
    // Unit Types
    // **********************************************************
    const UNIT_TYPE_FIFTEEN = '15m';  // converted to hourly units
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
    // Instance FUNCTIONS
    // **********************************************************

    /**
     * Get the number of units this instance authorizes and automatically
     * pull from the proper day of the week for specific days period types.
     * Note: This should be used instead of directly accessing the units property
     *
     * @param null|\Carbon\Carbon $date
     * @return null|float
     */
    public function getUnits(?Carbon $date = null): ?float
    {
        if ($this->period == self::PERIOD_SPECIFIC_DAYS) {
            return $this->unitsForDay(strtolower($date->format('l')));
        }

        if ($this->unit_type === self::UNIT_TYPE_FIFTEEN) {
            // Convert to hourly units
            return divide($this->units, 4);
        }

        return $this->units;
    }

    /**
     * Get the unit type for this authorization
     * Note: This should be used instead of directly accessing the unit_type property
     *
     * @return string
     */
    public function getUnitType(): string
    {
        if ($this->unit_type === self::UNIT_TYPE_FIFTEEN) {
            // Convert to hourly units
            return self::UNIT_TYPE_HOURLY;
        }

        return $this->unit_type;
    }

    /**
     * Get an array containing the start and end dates of the authorization
     * period.  Returns UTC dates to be accurate when querying shifts.
     *
     * @param \Carbon\Carbon $date
     * @return array|null
     */
    public function getPeriodDates($date) : ?array
    {
        switch ($this->period) {
            case self::PERIOD_DAILY:
                return [$date->copy()->startOfDay()->setTimezone('UTC'), $date->copy()->endOfDay()->setTimezone('UTC')];
                break;
            case self::PERIOD_WEEKLY:
                return [$date->copy()->startOfWeek()->setTimezone('UTC'), $date->copy()->endOfWeek()->setTimezone('UTC')];
                break;
            case self::PERIOD_MONTHLY:
                return [$date->copy()->startOfMonth()->setTimezone('UTC'), $date->copy()->endOfMonth()->setTimezone('UTC')];
                break;
            case self::PERIOD_TERM:
                return [Carbon::parse($this->effective_start)->setTimezone('UTC'), Carbon::parse($this->effective_end)->setTimezone('UTC')];
            case self::PERIOD_SPECIFIC_DAYS:
                if ($this->unitsForDay(strtolower($date->format('l'))) === null) {
                    // service auth does not covert the day of the week this shift is on so skip it
                    return [null, null];
                }
                return [$date->copy()->startOfDay()->setTimezone('UTC'), $date->copy()->endOfDay()->setTimezone('UTC')];
            default:
                return [null, null];
        }
    }

    public function unitsForDay($dayOfTheWeek) : ?int
    {
        if ($this->period != self::PERIOD_SPECIFIC_DAYS) {
            return null;
        }

        return $this->attributes[$dayOfTheWeek];
    }

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

}
