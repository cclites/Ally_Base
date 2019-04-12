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
                ->map(function ($schedule) {
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

    public function getShiftDates($shift) : array
    {
        $tz = $this->client->getTimezone();
        $start = $shift->checked_in_time->copy()->setTimezone($tz);
        $end = $shift->checked_out_time->copy()->setTimezone($tz);

        if ($start->format('Ymd') == $end->format('Ymd')) {
            return [$start];
        }

        // TODO: this does not properly handle shifts that expand more than two days
        return [$start, $end];
//        return CarbonPeriod::create($start->format('Y-m-d'), $end->format('Y-m-d'))->toArray();
    }

    public function getActiveServiceAuths(Shift $shift) : Collection
    {
        $auths = collect([]);

        foreach ($shift->getDateSpan() as $day) {
            $auths = $auths->push($this->client->getActiveServiceAuths($day));
        }

        return $auths;
    }

    /**
     * Check if the shift exceeds any client service authorizations that
     * were active during the time of the shift and return the
     * ClientAuthorization object that is exceeded.
     *
     * @param \App\Shift $shift
     * @return ClientAuthorization|null
     */
    public function exceededServiceAuthorization(Shift $shift) : ?ClientAuthorization
    {
        // Enumerate the shift dates and check service auths for all of them
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
        $authPeriodDates = $auth->getPeriodDates($shiftDate);
        $query = Shift::where('client_id', $this->client->id)
            ->where(function ($q) use ($authPeriodDates) {
                return $q->whereBetween('checked_in_time', $authPeriodDates)
                    ->whereBetween('checked_out_time', $authPeriodDates, 'OR');
            })
            ->where('fixed_rates', $auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED ? 1 : 0);

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
}
