<?php
namespace App\Shifts;

use App\Client;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;
use App\Billing\ClientAuthorization;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ServiceAuthValidator
{
    protected $includeSchedules;

    /**
     * @var \App\Client
     */
    protected $client;

    /**
     * ServiceAuthValidator constructor.
     * @param \App\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function shiftExceedsMaxClientHours(Shift $shift)
    {
        $date = $shift->checked_in_time->copy()->setTimezone($this->client->getTimezone());
        $period = [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()];

        return $this->exceedsMaxClientHours($period, false);
    }

    public function scheduleExceedsMaxClientHours(Schedule $schedule)
    {
        $period = [$schedule->starts_at->copy()->startOfWeek(), $schedule->starts_at->copy()->endOfWeek()];

        return $this->exceedsMaxClientHours($period, true, $schedule);
    }

    /**
     * Check if the shift would exceed the client weekly hours limit.
     *
     * @param array $period
     * @param bool $includeSchedules
     * @param Schedule|null $schedule
     * @return bool
     */
    public function exceedsMaxClientHours(array $period, bool $includeSchedules = false, Schedule $schedule = null) : bool
    {
        $total = Shift::where('client_id', $this->client->id)
            ->whereBetween('checked_in_time', $period)
            ->get()
            ->map(function ($shift) {
                return $shift->getBillableHours();
            })
            ->sum();

        if ($includeSchedules) {
            $scheduleQuery = Schedule::where('client_id', $this->client->id)
                ->whereBetween('starts_at', $period);

            if ($schedule && !empty($schedule->id)) {
                $scheduleQuery->where('id', '<>', $schedule->id);
            }

            $total += $scheduleQuery
                ->get()
                ->map(function (Schedule $schedule) {
                    return $schedule->getBillableHours();
                })
                ->sum();

            if ($schedule) {
                $total += $schedule->getBillableHours();
            }
        }

        if ($total > $this->client->max_weekly_hours) {
            return true;
        }

        return false;
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
        // Enumerate the shift dates and check service auths for all of them
        dd($shift->getDateSpan());
        foreach ($shift->getDateSpan() as $day) {
            foreach ($this->client->getActiveServiceAuths($day) as $auth) {
                $query = $this->getMatchingShiftsQuery($auth, $day);

                if ($auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
                    // If fixed limit then just check the count of the fixed shifts
                    if ($query->count() > $auth->getUnits($day)) {
                        return $auth;
                    }
                } else {
                    // Get total hours billed for each shift on this date only
                    $total = $query->get()
                        ->map(function (Shift $item) use ($day, $auth) {
                            return $item->getBillableHoursForDay($day, $auth->service_id, $auth->payer_id);
                        })
                        ->sum();

                    // Check service auth units
                    if ($total > $auth->getUnits($day)) {
                        return $auth;
                    }
                }
            }
        }

        return null;
    }

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
                            return $item->getBillableHoursForDay($day, $auth->service_id, $auth->payer_id);
                        })
                        ->sum();

                    // Add total hours for all schedules during this period
                    $total += $this->getMatchingSchedulesQuery($auth, $day, $schedule->id)
                        ->get()
                        ->map(function (Schedule $item) use ($day, $auth) {
                            return $item->getBillableHoursForDay($day, $auth->service_id, $auth->payer_id);
                        })
                        ->sum();

                    // Add total of billable hours on the schedule being checked
                    // since those changes may not be persisted yet.
                    $total += $schedule->getBillableHoursForDay($day, $auth->service_id, $auth->payer_id);

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
     * @param ClientAuthorization $auth
     * @param \Carbon\Carbon $shiftDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getMatchingShiftsQuery(ClientAuthorization $auth, Carbon $shiftDate) : Builder
    {
        $authPeriodDates = $auth->getPeriodDates($shiftDate, 'UTC');

        print_r($authPeriodDates);
        print_r(Shift::all()->pluck('checked_in_time', 'checked_out_time'));
        $query = Shift::where('client_id', $this->client->id)
            ->where(function ($q) use ($authPeriodDates) {
                return $q->whereBetween('checked_in_time', $authPeriodDates)
                    ->whereBetween('checked_out_time', $authPeriodDates, 'OR');
            });

        if ($auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
            // Only query for fixed rates when client auth is set to fixed,
            // otherwise you want to include all shifts and count the hours
            $query->where('fixed_rates', 1);
        }

        // Must match service and/or payer
        $query->where(function($q) use ($auth) {
            $q->where(function($q3) use ($auth) {
                $q3->where('service_id', $auth->service_id);
                if (! empty($auth->payer_id)) {
                    $q3->where('payer_id', $auth->payer_id);
                }
            })->orWhereHas('services', function ($q2) use ($auth) {
                    $q2->where('service_id', $auth->service_id);
                    if (! empty($auth->payer_id)) {
                        $q2->where('payer_id', $auth->payer_id);
                    }
                });
        });

        return $query;
    }

    protected function getMatchingSchedulesQuery(ClientAuthorization $auth, Carbon $date, ?int $ignoreId = null) : Builder
    {
        $authPeriodDates = $auth->getPeriodDates($date, $this->client->getTimezone());

        if (config('app.env') === 'testing') {
            $endsAtQuery = \DB::raw("DATETIME(starts_at, printf('+%s minute', duration)) as ends_at");
        } else {
            $endsAtQuery = \DB::raw('ADDTIME(starts_at, INTERVAL duration MINUTE) as ends_at');
        }

        $query = Schedule::select([\DB::raw('*'), $endsAtQuery])
            ->where('client_id', $this->client->id)
            ->whereDoesntHave('shifts')
            ->where(function ($q) use ($authPeriodDates) {
                return $q->whereBetween('starts_at', $authPeriodDates)
                    ->whereBetween('ends_at', $authPeriodDates, 'OR');
            });

        if ($auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
            // Only query for fixed rates when client auth is set to fixed,
            // otherwise you want to include all shifts and count the hours
            $query->where('fixed_rates', 1);
        }

        // Must match service and/or payer
        $query->where(function($q) use ($auth) {
            $q->where(function($q3) use ($auth) {
                $q3->where('service_id', $auth->service_id);
                if (! empty($auth->payer_id)) {
                    $q3->where('payer_id', $auth->payer_id);
                }
            })->orWhereHas('services', function ($q2) use ($auth) {
                    $q2->where('service_id', $auth->service_id);
                    if (! empty($auth->payer_id)) {
                        $q2->where('payer_id', $auth->payer_id);
                    }
                });
        });

        if (! empty($ignoreId)) {
            $query->whereNotIn('id', [$ignoreId]);
        }

        return $query;
    }
}
