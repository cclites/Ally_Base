<?php
namespace App\Reports;

use App\Billing\GatewayTransaction;
use App\Shifts\AllyFeeCalculator;
use App\Shift;
use App\Shifts\RateFactory;
use App\Shifts\Rates;
use App\Traits\ShiftReportFilters;
use Illuminate\Support\Str;

class ShiftsReport extends BusinessResourceReport
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
        $this->query = Shift::with(['business', 'caregiver', 'client', 'statusHistory', 'goals', 'questions', 'costHistory', 'client.defaultPayment']);
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
        $shifts = $this->query->get();
        $this->generated = true;
        $rows = $shifts->map(function(Shift $shift) {
            $rates = $this->getRates($shift);
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
                'fixed_rates' => $shift->fixed_rates,
                'caregiver_rate' => number_format($rates->hourly->caregiver_rate, 2),
                'provider_fee' => number_format($rates->hourly->provider_fee, 2),
                'ally_fee' => number_format($rates->hourly->ally_fee, 2),
                'hourly_total' => number_format($rates->hourly->client_rate, 2),
                'other_expenses' => number_format($shift->other_expenses, 2),
                'mileage' => number_format($shift->mileage, 2),
                'mileage_costs' => number_format($shift->costs()->getMileageCost(), 2),
                'caregiver_total' => number_format($rates->total->caregiver_rate, 2),
                'provider_total' => number_format($rates->total->provider_fee, 2),
                'ally_total' => number_format($rates->total->ally_fee, 2),
                'ally_pct' => $shift->getAllyPercentage(),
                'shift_total' => number_format($rates->total->client_rate, 2),
                'hours_type' => $shift->hours_type,
                'confirmed' => $shift->statusManager()->isConfirmed(),
                'confirmed_at' => $shift->confirmed_at,
                'client_confirmed' => $shift->client_confirmed,
                'charged' => !($shift->statusManager()->isPending()),
                'charged_at' => $shift->charged_at,
                'status' => $shift->status ? Str::title(preg_replace('/_/', ' ', $shift->status)) : '',
                // Send both verified and EVV for backwards compatibility
                'verified' => $shift->verified,
                'EVV' => ($shift->checked_in_verified && $shift->checked_out_verified),
                'goals' => $shift->goals,
                'questions' => $shift->questions,
                'flags' => $shift->flags,
                'created_at' => optional($shift->created_at)->toDateTimeString(),
            ];
            return $row;
        });
        return $rows;
    }

    /**
     * Count the number of rows
     *
     * @return int
     */
    public function count()
    {
        if ($this->rows) return $this->rows->count();
        return $this->query()->count();
    }

    protected function getRates(Shift $shift)
    {
        $rates = new ShiftsReportRates();
        $rates->hourly = $shift->costs()->getHourlyRates();
        $rates->total = $shift->costs()->getTotalRates();

        return $rates;
    }
}

class ShiftsReportRates {
    /** @var \App\Shifts\Rates */
    public $hourly;
    /** @var \App\Shifts\Rates */
    public $total;
}