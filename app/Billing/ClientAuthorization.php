<?php

namespace App\Billing;

use App\AuditableModel;
use App\Shifts\ServiceAuthCalculator;
use Carbon\Carbon;
use App\Client;
use Carbon\CarbonPeriod;

/**
 * App\Billing\ClientAuthorization
 *
 * @property int $id
 * @property int $client_id
 * @property int $service_id
 * @property string|null $service_auth_id
 * @property string $effective_start
 * @property string $effective_end
 * @property float $units
 * @property string $unit_type
 * @property string $period
 * @property int $week_start
 * @property float $sunday
 * @property float $monday
 * @property float $tuesday
 * @property float $wednesday
 * @property float $thursday
 * @property float $friday
 * @property float $saturday
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \App\Billing\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientAuthorization effectiveOn(\Carbon\Carbon $date)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientAuthorization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientAuthorization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\ClientAuthorization query()
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
     * convert to hours.
     *
     * @param null|\Carbon\Carbon $date
     * @return null|float
     */
    public function getHours(?Carbon $date = null): ?float
    {
        $units = $this->getUnits($date);

        if ($this->unit_type === self::UNIT_TYPE_FIFTEEN) {
            // Convert to hourly units
            return divide($units, 4);
        }

        return $units;
    }

    /**
     * Get the number of units this instance authorizes and automatically
     * pull from the proper day of the week for specific days period types.
     * Note: This should be used instead of directly accessing the units property
     *
     * @param Carbon|null $date
     * @return float|null
     */
    public function getUnits(?Carbon $date = null): ?float
    {
        if ($this->period == self::PERIOD_SPECIFIC_DAYS) {
            if (empty($date)) {
                return floatval(0);
            }
            return $this->unitsForDay(strtolower($date->format('l')));
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
     * @param string $timezone
     * @return array|null
     */
    public function getPeriodDates(Carbon $date, string $timezone = 'UTC') : ?array
    {
        switch ($this->period) {
            case self::PERIOD_DAILY:
                return [$date->copy()->startOfDay()->setTimezone($timezone), $date->copy()->endOfDay()->setTimezone($timezone)];
                break;
            case self::PERIOD_WEEKLY:
                return alterStartOfWeekDay((int) $this->week_start, function() use ($date, $timezone) {
                    return [$date->copy()->startOfWeek()->setTimezone($timezone), $date->copy()->endOfWeek()->setTimezone($timezone)];
                });
            case self::PERIOD_MONTHLY:
                return [$date->copy()->startOfMonth()->setTimezone($timezone), $date->copy()->endOfMonth()->setTimezone($timezone)];
            case self::PERIOD_TERM:
                return [Carbon::parse($this->effective_start)->setTimezone($timezone), Carbon::parse($this->effective_end)->setTimezone($timezone)];
            case self::PERIOD_SPECIFIC_DAYS:
                return [$date->copy()->startOfDay()->setTimezone($timezone), $date->copy()->endOfDay()->setTimezone($timezone)];
            default:
                return [null, null];
        }
    }

    public function getPeriodsForRange(Carbon $start, Carbon $end) : array
    {
        $periods = collect([]);
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (! $this->isEffectiveOn($date)) {
                continue;
            }

            list($start, $end) = $this->getPeriodDates($date);

            $periods->push([$start, $end]);
        }

        return $periods->unique()->toArray();
    }

    /**
     * Get the units for the day of the week based on
     * the daily settings on the model.
     *
     * @param string $dayOfTheWeek
     * @return int|null
     */
    public function unitsForDay(string $dayOfTheWeek) : ?int
    {
        if ($this->period != self::PERIOD_SPECIFIC_DAYS) {
            return null;
        }

        return $this->attributes[$dayOfTheWeek];
    }

    /**
     * Get the number of used units from total number of hours.
     *
     * @param float $hours
     * @return float
     */
    public function getUnitsFromHours(float $hours) : float
    {
        if ($this->unit_type == self::UNIT_TYPE_FIFTEEN) {
            return multiply($hours,4);
        }

        // Otherwise units are hourly units.
        return $hours;
    }

    /**
     * Get the number of used hours from total number of units.
     *
     * @param float $units
     * @return float
     */
    public function getHoursFromUnits(float $units) : float
    {
        if ($units === floatval(0)) {
            return floatval(0);
        }

        if ($this->unit_type == self::UNIT_TYPE_FIFTEEN) {
            return divide(multiply($units,15), 60);
        }

        // Otherwise units are hourly units.
        return $units;
    }

    /**
     * Get an instance of the Authorization's ServiceAuthCalculator.
     *
     * @return ServiceAuthCalculator
     */
    public function getCalculator() : ServiceAuthCalculator
    {
        return new ServiceAuthCalculator($this);
    }

    /**
     * Check if this auth is effective for a given date.
     *
     * @param Carbon $date
     * @return bool
     */
    public function isEffectiveOn(Carbon $date) : bool
    {
        $start = Carbon::parse($this->effective_start)->setTime(0, 0, 0);
        $end = Carbon::parse($this->effective_end)->setTime(23, 59, 59);
        return $date->between($start, $end);
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Get only the service authorizations effective during the
     * given date.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Carbon\Carbon $date
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeEffectiveOn($query, Carbon $date)
    {
        return $query->where('effective_start', '<=', $date->toDateString())
            ->where('effective_end', '>=', $date->toDateString());
    }

    /**
     * Get authorizations that were effective anywhere during
     * the given date range.
     *
     * @param $query
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    public function scopeEffectiveDuringRange($query, Carbon $start, Carbon $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->where(function ($q) use ($start, $end) {
                $q->where('effective_start', '<=', $start->toDateString())
                    ->orWhere('effective_start', '<=', $end->toDateString());
            })
            ->where(function ($q) use ($start, $end) {
                $q->where('effective_end', '>=', $start->toDateString())
                    ->orWhere('effective_end', '>=', $end->toDateString());
            });
        });
    }
}
