<?php
namespace App\Reports;

use App\GatewayTransaction;
use App\Shifts\AllyFeeCalculator;
use App\Shift;
use App\Traits\ShiftReportFilters;

class ShiftsReport extends BaseReport
{
    use ShiftReportFilters;

    /**
     * @var bool
     */
    protected $generated = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * ScheduledPaymentsReport constructor.
     */
    public function __construct()
    {
        $this->query = Shift::query();
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $shifts = $this->query->with(['caregiver', 'client'])->get();
        $rows = $shifts->map(function(Shift $shift) {
            $allyFee = AllyFeeCalculator::getHourlyRate($shift->client, null, $shift->caregiver_rate, $shift->provider_fee);
            $row = [
                'id' => $shift->id,
                'checked_in_time' => $shift->checked_in_time->format('c'),
                'checked_out_time' => optional($shift->checked_out_time)->format('c'),
                'hours' => $shift->duration(),
                'client_id' => $shift->client_id,
                'business_id' => $shift->business_id,
                'client_name' => optional($shift->client)->nameLastFirst(),
                'caregiver_id' => $shift->caregiver_id,
                'caregiver_name' => optional($shift->caregiver)->nameLastFirst(),
                'caregiver_rate' => $shift->caregiver_rate,
                'provider_fee' => $shift->provider_fee,
                'ally_fee' => number_format($allyFee, 2),
                'hourly_total' => number_format($shift->caregiver_rate + $shift->provider_fee + $allyFee, 2),
                'other_expenses' => number_format($shift->other_expenses, 2),
                'mileage' => number_format($shift->mileage, 2),
                'mileage_costs' => number_format($shift->costs()->getMileageCost(), 2),
                'caregiver_total' => number_format($shift->costs()->getCaregiverCost(), 2),
                'provider_total' => number_format($shift->costs()->getProviderFee(), 2),
                'ally_total' => number_format($shift->costs()->getAllyFee(), 2),
                'ally_pct' => AllyFeeCalculator::getPercentage($shift->client, null),
                'shift_total' => number_format($shift->costs()->getTotalCost(), 2),
                'hours_type' => $shift->hours_type,
                'confirmed' => $shift->statusManager()->isConfirmed(),
                'confirmed_at' => optional($shift->statusManager()->confirmedAt())->format('c'),
                'status' => $shift->status ? title_case(preg_replace('/_/', ' ', $shift->status)) : '',
                'EVV' => $shift->verified,
            ];
            return $row;
        });
        return $rows;
    }
}