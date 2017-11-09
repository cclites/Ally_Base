<?php
namespace App\Reports;

use App\Contracts\Report;
use App\Shift;
use Carbon\Carbon;

class ScheduledPaymentsReport extends ShiftsReport
{

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rows()
    {
        if (!$this->generated) {
            $shifts = $this->query->with(['caregiver', 'client'])
                ->whereIn('status', [Shift::WAITING_FOR_AUTHORIZATION, Shift::WAITING_FOR_CHARGE, Shift::WAITING_FOR_APPROVAL])
                ->get();
            $this->rows = $shifts->map(function(Shift $shift) {
                return [
                    'shift_id' => $shift->id,
                    'shift_time' => (new Carbon($shift->checked_in_time))->format(DATE_ISO8601),
                    'shift_hours' => $shift->duration(),
                    'client' => [
                        'id' => $shift->client->id,
                        'name' => $shift->client->nameLastFirst(),
                    ],
                    'caregiver' => [
                        'id' => $shift->caregiver->id,
                        'name' => $shift->caregiver->nameLastFirst(),
                    ],
                    'total_payment' => $shift->costs()->getTotalCost(),
                    'business_allotment' => $shift->costs()->getProviderFee(),
                    'ally_allotment' => $shift->costs()->getAllyFee(),
                    'caregiver_allotment' => $shift->costs()->getCaregiverCost(),
                ];
            });
        }
        return $this->rows;
    }

}