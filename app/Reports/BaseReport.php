<?php
namespace App\Reports;

use App\Contracts\Report;
use App\Shift;
use Carbon\Carbon;

abstract class BaseReport implements Report
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
    abstract public function query();

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public function rows();

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

    /**
     * Specify the sort order for the report
     *
     * @param $column
     * @param string $direction ASC | DESC
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->query()->orderBy($column, $direction);
        return $this;
    }
}