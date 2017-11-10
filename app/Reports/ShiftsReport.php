<?php
namespace App\Reports;

use App\Contracts\Report;
use App\Scheduling\AllyFeeCalculator;
use App\Shift;
use Carbon\Carbon;

class ShiftsReport extends BaseReport
{
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
    public function rows()
    {
        if (!$this->generated) {
            $shifts = $this->query->with(['caregiver', 'client'])->get();
            $this->rows = $shifts->map(function(Shift $shift) {
                $allyFee = AllyFeeCalculator::getFee($shift->client, null, $shift->caregiver_rate + $shift->provider_fee);
                $row = array_merge($shift->toArray(), [
                    'ally_fee' => number_format($allyFee, 2),
                    'shift_total' => number_format($shift->costs()->getTotalCost(), 2),
                    'hourly_total' => number_format($shift->caregiver_rate + $shift->provider_fee + $allyFee, 2),
                    'payment_method' => 'TBD',
                ]);
                return $row;
            });
        }
        return $this->rows;
    }

}