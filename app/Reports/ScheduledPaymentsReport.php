<?php
namespace App\Reports;

use App\Shifts\AllyFeeCalculator;
use App\Shift;
use Carbon\Carbon;

class ScheduledPaymentsReport extends ShiftsReport
{

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $shifts = $this->query->with(['caregiver', 'client'])
            ->whereIn('status', [Shift::WAITING_FOR_AUTHORIZATION, Shift::WAITING_FOR_CHARGE])
            ->get();
        $rows = $shifts->map(function(Shift $shift) {
            return [
                'shift_id' => $shift->id,
                'shift_time' => (new Carbon($shift->checked_in_time))->format(DATE_ISO8601),
                'shift_hours' => $shift->duration(),
                'status' => $shift->status,
                'client_id' => $shift->client_id,
                'client' => [
                    'id' => $shift->client->id,
                    'name' => $shift->client->nameLastFirst(),
                ],
                'caregiver' => [
                    'id' => $shift->caregiver->id,
                    'name' => $shift->caregiver->nameLastFirst(),
                ],
                'payment_type' => $shift->client->getPaymentType(),
                'ally_pct' => AllyFeeCalculator::getPercentage($shift->client, null),
                'total_payment' => number_format($shift->costs()->getTotalCost(), 2),
                'business_allotment' => number_format($shift->costs()->getProviderFee(), 2),
                'ally_allotment' => number_format($shift->costs()->getAllyFee(), 2),
                'caregiver_allotment' => number_format($shift->costs()->getCaregiverCost(), 2),
                'mileage' => $shift->mileage,
                'mileage_costs' => number_format($shift->costs()->getMileageCost(), 2),
                'verified' => $shift->verified,
            ];
        });
        return $rows;
    }

}