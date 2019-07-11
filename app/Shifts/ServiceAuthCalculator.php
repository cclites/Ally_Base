<?php
namespace App\Shifts;

use App\Client;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;
use App\Billing\ClientAuthorization;
use Illuminate\Database\Eloquent\Builder;

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

    public function getUtilizationFromQuery($query) : float
    {
        if ($this->auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
            // If fixed limit then just check the count of the fixed shifts
            return floatval($query->count());
        } else {
            // Get total hours billed for each shift on this date only
            return $query->get()
                ->reduce(function (float $carry, $item) {
                    return add($carry, $item->getBillableHours($this->auth->service_id));
                }, floatval(0.0));
        }
    }

    /**
     * Check if the shift exceeds any client service authorizations that
     * were active during the time of the shift and return the
     * ClientAuthorization object that is exceeded.
     *
     * @param \App\Shift $shift
     * @return ClientAuthorization|null
     */
    public function shiftExceedsServiceAuthorization(Shift $shift) : ?ClientAuthorization
    {
        $query = $this->getMatchingShiftsQuery($this->auth, $day);

        if ($this->auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
            // If fixed limit then just check the count of the fixed shifts
            if ($query->count() > $this->auth->getUnits($day)) {
                return $this->auth;
            }
        } else {
            // Get total hours billed for each shift on this date only
            $totalHours = $query->get()
                ->map(function (Shift $item) use ($day) {
                    return $item->getBillableHoursForDay($day, $this->auth->service_id);
                })
                ->sum();

            // Check service auth units
            if ($total > $this->auth->getUnits($day)) {
                return $this->auth;
            }
        }

        return null;
    }

    /**
     * Check if the given schedule exceeds an existing service authorizations and
     * return the ClientAuthorization object.
     *
     * @param Schedule $schedule
     * @return ClientAuthorization|null
     */
    public function scheduleExceedsServiceAuthorization(Schedule $schedule) : ?ClientAuthorization
    {
        // Enumerate the shift dates and check service auths for all of them
        foreach ($schedule->getDateSpan() as $day) {
            foreach ($this->client->getActiveServiceAuths($day) as $auth) {
                if ($auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
                    // If fixed limit then just check the count of the fixed shifts
                    $total = 1; // the current schedule
                    $total += $this->getMatchingShiftsQuery($auth, $day)->count();
                    $total += $this->getMatchingSchedulesQuery($auth, $day, $schedule->id)->count();
                    if ($total > $auth->getUnits($day)) {
                        return $auth;
                    }
                } else {
                    // Get total hours for each shift on this date only
                    $total = $this->getMatchingShiftsQuery($auth, $day)
                        ->get()
                        ->map(function (Shift $item) use ($day, $auth) {
                            return $item->getBillableHoursForDay($day, $auth->service_id);
                        })
                        ->sum();

                    // Add total hours for all schedules during this period
                    $total += $this->getMatchingSchedulesQuery($auth, $day, $schedule->id)
                        ->get()
                        ->map(function (Schedule $item) use ($day, $auth) {
                            return $item->getBillableHoursForDay($day, $auth->service_id);
                        })
                        ->sum();

                    // Add total of billable hours on the schedule being checked
                    // since those changes may not be persisted yet.
                    $total += $schedule->getBillableHoursForDay($day, $auth->service_id);

                    // Check total against service auth units
                    if ($total > $auth->getUnits($day)) {
                        return $auth;
                    }
                }
            }
        }

        return null;
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
        $query = Shift::with('services', 'service')
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
        } else {
            $query->where('fixed_rates', 0);
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
        } else {
            $query->where('fixed_rates', 0);
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
