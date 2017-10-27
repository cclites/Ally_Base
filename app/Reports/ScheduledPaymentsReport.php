<?php
namespace App\Reports;

use App\Contracts\Report;
use App\Shift;
use Carbon\Carbon;

class ScheduledPaymentsReport implements Report
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
     * Add a condition to limit report data
     *
     * @param $field
     * @param $delimiter
     * @param null $value
     * @return $this
     */
    public function where($field, $delimiter, $value = null)
    {
        $this->query->where($field, $delimiter, $value);
        return $this;
    }

    /**
     * Limit rows between two dates
     *
     * @param string|\DateTime|null $start If null, leave starting period unlimited
     * @param string|\DateTime|null $end If null, leave ending period unlimited
     * @return $this
     */
    public function between($start = null, $end = null)
    {
        if ($start && $end) {
            $this->query->whereBetween('checked_in_time', [$start, $end]);
        }
        elseif ($start) {
            $this->query->where('checked_in_time', '>=', $start);
        }
        else {
            $this->query->where('checked_in_time', '<=', $end);
        }
        return $this;
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

    /**
     * Count the number of rows
     *
     * @return int
     */
    public function count()
    {
        return $this->rows()->count();
    }

    /**
     * Return the sum of a column
     *
     * @return float
     */
    public function sum($column)
    {
        return $this->rows()->sum($column);
    }
}