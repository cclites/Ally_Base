<?php
namespace App\Shifts;

use App\Shift;
use Carbon\Carbon;
use App\Billing\ClientAuthorization;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

class ServiceAuthValidator
{
    /**
     * @var \App\Shift
     */
    protected $shift;

    /**
     * Create a new instance.
     *
     * @param \App\Shift $shift
     */
    public function __construct(Shift $shift)
    {
        $this->shift = $shift;
    }

    /**
     * Check if the shift would exceed the client weekly hours limit.
     *
     * @return boolean
     */
    public function exceedsMaxClientHours() : bool
    {
        // Check if shift would exceed clients max hours
        $period = [
            $this->getRelativeShiftTime()->startOfWeek(),
            $this->getRelativeShiftTime()->endOfWeek()
        ];

        $shifts = Shift::where('client_id', $this->shift->client_id)
            ->whereBetween('checked_in_time', $period)
            ->get();

        $total = 0;
        foreach ($shifts as $shift) {
            $total += $shift->getBillableHours();
        }

        if ($total > $this->shift->client->max_weekly_hours) {
            return true;
        }

        return false;
    }

    public function getShiftDates($shift)
    {
        $tz = $shift->client->getTimezone();
        $start = $shift->checked_in_time->copy()->setTimezone($tz);
        $end = $shift->checked_out_time->copy()->setTimezone($tz);

        if ($start->format('Ymd') == $end->format('Ymd')) {
            return [$start];
        }

        // TODO: this does not properly handle shifts that expand more than two days
        return [$start, $end];
//        return CarbonPeriod::create($start->format('Y-m-d'), $end->format('Y-m-d'))->toArray();
    }

    public function getBilledHoursForDay($shift, $date, $auth) : float
    {
        $tz = $shift->client->getTimezone();
        $start = $shift->checked_in_time->copy()->setTimezone($tz);
        $end = $shift->checked_out_time->copy()->setTimezone($tz);

        $hours = $shift->getBillableHours($auth->service_id, $auth->payer_id);

        $shiftDates = $this->getShiftDates($shift);
        if (count($shiftDates) === 1) {
            return $hours;
        }

        // TODO: this does not properly handle shifts that expand more than two days

        if ($start->format('Ymd') === $date->format('Ymd')) {
            $minutes = $start->diffInMinutes($start->copy()->endOfDay());
            return $minutes === 0 ? 0 : ($minutes / 60);
        } else {
            $minutes = $end->copy()->startOfDay()->diffInMinutes($end);
            return $minutes === 0 ? 0 : ($minutes / 60);
        }
//        for ($i = 0; $i < count($shiftDates); $i++) {
//            $day = $shiftDates[$i];
//            if ($day->format('Ymd') == $date->format('Ymd')) {
//                // this is the day we want hours for
//                dd($i);
//                if ($i === 0) {
//                    // first day of the shift, get the hours from the checked
//                    // in time up until the end of the first day
//                    dd($i);
//                    return $start->diffInHours($start->copy()->endOfDay());
//                }
//                else if ($i === count($shiftDates) - 1) {
//                    // last day of the shift, get hours from start of the last time
//                    // up until the checked out time
//                    return $end->copy()->startOfDay()->diffInHours($end);
//                } else {
//                    // middle day - no shift should really expand more than two days
//                    // but if it does this would = 24 hours
//                    return 24;
//                }
//            }
//        }

        // should not reach here
        return 0;
    }

    /**
     * Check if the shift exceeds any client service authorizations that
     * were active during the time of the shift and return the
     * ClientAuthorization object that is exceeded.
     *
     * @return ClientAuthorization|null
     */
    public function exceededServiceAuthorization() : ?ClientAuthorization
    {
        foreach ($this->shift->getActiveServiceAuths() as $auth) {
            if ($auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED) {
                // If fixed limit then just check the count of the fixed shifts
                if ($this->getMatchingShiftsQuery($auth, $this->getRelativeShiftTime())->count() > $auth->getUnits($this->getRelativeShiftTime())) {
                    return $auth;
                }
            } else {
                // Get an array of dates in which the shift exists on
                $days = $this->getShiftDates($this->shift);

                // Enumerate the shift dates and check service auths for all of them
                foreach ($days as $day) {
                    // Get all shifts that exist on this date
                    $shifts = $this->getMatchingShiftsQuery($auth, $day)->get();

                    // Get total hours billed for each shift on this date only
                    $total = 0;
                    foreach ($shifts as $s) {
                        $total += $this->getBilledHoursForDay($s, $day, $auth);
                    }

                    // Check service auth units
//                    echo "day: " . $day->toDateTimeString() . " - total: $total - units: " . $auth->getUnits($day) . "\r\n";
                    if ($total > $auth->getUnits($day)) {
                        return $auth;
                    }
                }

                // Get all shifts that either start or end inside the auth's effective range
//                $shifts = $this->getMatchingShiftsQuery($auth)->get();

                // Get the total hours billed in the auth's effective range

//                $total = 0;
//                foreach ($shifts as $shift) {
//                    $total += $shift->getBillableHours($auth->service_id, $auth->payer_id);
//                }
//
//                if ($total > $auth->getUnits($this->getRelativeShiftTime())) {
//                    return $auth;
//                }
            }
        }

        return null;
    }

    /**
     * Build query to get the shifts that match the attributes of the 
     * specified ClientAuthorization.
     *
     * @param ClientAuthorization $auth
     * @return Illuminate\Database\Eloquent\Builder
     */
    protected function getMatchingShiftsQuery(ClientAuthorization $auth, $shiftDate) : Builder
    {
        $authPeriodDates = $auth->getPeriodDates($shiftDate);
        $query = Shift::where('client_id', $this->shift->client_id)
            ->where(function ($q) use ($authPeriodDates) {
                return $q->whereBetween('checked_in_time', $authPeriodDates)
                    ->whereBetween('checked_out_time', $authPeriodDates, 'OR');
            })
            ->where('fixed_rates', $auth->getUnitType() === ClientAuthorization::UNIT_TYPE_FIXED ? 1 : 0);

        // Must match service
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

    /**
     * Get the time of the current shift, based on the Client's timezone.
     *
     * @return \Carbon\Carbon
     */
    public function getRelativeShiftTime() : Carbon
    {
        return $this->shift->checked_in_time
            ->copy()
            ->setTimezone($this->shift->client->getTimezone());
    }
}
