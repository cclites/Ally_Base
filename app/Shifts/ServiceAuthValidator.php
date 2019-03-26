<?php
namespace App\Shifts;

use App\Shift;
use Carbon\Carbon;
use App\Billing\ClientAuthorization;
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
                if ($this->getMatchingShiftsQuery($auth)->count() > $auth->getUnits($this->getRelativeShiftTime())) {
                    return $auth;
                }
            } else {
                // Calculate the duration of the shifts to measure hourly units
                $shifts = $this->getMatchingShiftsQuery($auth)->get();

                $total = 0;
                foreach ($shifts as $shift) {
                    $total += $shift->getBillableHours($auth->service_id, $auth->payer_id);
                }

                if ($total > $auth->getUnits($this->getRelativeShiftTime())) {
                    return $auth;
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
     * @return Illuminate\Database\Eloquent\Builder
     */
    protected function getMatchingShiftsQuery(ClientAuthorization $auth) : Builder
    {
        $query = Shift::where('client_id', $this->shift->client_id)
            ->whereBetween('checked_in_time', $auth->getPeriodDates($this->getRelativeShiftTime()))
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
