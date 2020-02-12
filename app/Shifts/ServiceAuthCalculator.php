<?php
namespace App\Shifts;

use App\Schedule;
use App\Shift;
use App\Billing\ClientAuthorization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ServiceAuthCalculator
{
    /**
     * @var ClientAuthorization
     */
    protected $auth;

    /**
     * ServiceAuthValidator constructor.
     * @param ClientAuthorization $clientAuthorization
     */
    public function __construct(ClientAuthorization $clientAuthorization)
    {
        $this->auth = $clientAuthorization;
    }

    public function getMatchingShifts(array $forPeriod) : Collection
    {
        return $this->getMatchingShiftsQuery($forPeriod)
            ->get();
    }

    public function getConfirmedUsage(array $forPeriod) : float
    {
        $query = $this->getMatchingShiftsQuery($forPeriod)
            ->whereConfirmed();

        return $this->getUtilizationFromQuery($query);
    }

    public function getUnconfirmedUsage(array $forPeriod) : float
    {
        $query = $this->getMatchingShiftsQuery($forPeriod)
            ->whereUnconfirmed();

        return $this->getUtilizationFromQuery($query);
    }

    public function getScheduledUsage(array $forPeriod) : float
    {
        $query = $this->getMatchingSchedulesQuery($forPeriod);

        return $this->getUtilizationFromQuery($query);
    }

    /**
     * Query the matching shifts or schedules and get the number
     * of units used for this service authorization.
     *
     * @param Builder $query
     * @return float
     */
    public function getUtilizationFromQuery(Builder $query) : float
    {
        if ($this->auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
            // If fixed limit then just check the count of the fixed shifts
            return floatval($query->count());
        } else {
            // Get total hours billed for each shift on this date only
            $hours = $query->get()
                ->reduce(function (float $carry, $item) {
                    // TODO: Create an interface for this that contains the getBillableHours method
                    return add($carry, $item->getBillableHours($this->auth->service_id));
                }, floatval(0.0));

            return $this->auth->getUnitsFromHours($hours);
        }
    }

    /**
     * Build query to get the shifts that match the attributes of the
     * specified ClientAuthorization.
     *
     * @param array $authPeriodDates
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getMatchingShiftsQuery(array $authPeriodDates) : Builder
    {
        $query = Shift::with('services', 'service', 'client', 'client.user', 'shiftFlags')
            ->where('client_id', $this->auth->client_id)
            ->whereNotNull('checked_out_time')
            ->where(function ($q) use ($authPeriodDates) {
                return $q->whereBetween('checked_in_time', $authPeriodDates)
                    ->whereBetween('checked_out_time', $authPeriodDates, 'OR');
            });

        if ($this->auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
            // Only query for fixed rates when client auth is set to fixed,
            // otherwise you want to include all shifts and count the hours
            $query->where('fixed_rates', 1);
        }

        // Must match service
        $query->where(function($q) {
            $q->where(function($q3) {
                $q3->where('service_id', '=', $this->auth->service_id);
            })->orWhereHas('services', function ($q2) {
                $q2->where('service_id', '=', $this->auth->service_id);
            });
        });

        return $query;
    }

    /**
     * Build query to get the schedules that match the attributes of the
     * specified ClientAuthorization.
     *
     * @param array $authPeriodDates
     * @return Builder
     */
    protected function getMatchingSchedulesQuery(array $authPeriodDates) : Builder
    {
        // get the proper "ends_at" SQL syntax depending on the database type (MySQL/SQLite)
        if (config('app.env') === 'testing') {
            $endsAt = \DB::raw("DATETIME(starts_at, printf('+%s minute', duration))");
        } else {
            $endsAt = \DB::raw('DATE_ADD(starts_at, INTERVAL duration MINUTE)');
        }

        $query = Schedule::where('client_id', $this->auth->client_id)
            ->whereDoesntHave('shifts')
            ->where(function ($q) use ($authPeriodDates, $endsAt) {
                return $q->whereBetween('starts_at', $authPeriodDates)
                    ->whereBetween($endsAt, $authPeriodDates, 'OR');
            });

        if ($this->auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
            // Only query for fixed rates when client auth is set to fixed,
            // otherwise you want to include all shifts and count the hours
            $query->where('fixed_rates', 1);
        }

        // Must match service
        $query->where(function($q) {
            $q->where(function($q3) {
                $q3->where('service_id', $this->auth->service_id);
            })->orWhereHas('services', function ($q2) {
                    $q2->where('service_id', $this->auth->service_id);
                });
        });

        return $query;
    }
}
