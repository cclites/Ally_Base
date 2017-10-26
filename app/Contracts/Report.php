<?php


namespace App\Contracts;

interface Report
{
    /**
     * Add a condition to limit report data
     *
     * @param $field
     * @param $delimiter
     * @param null $value
     * @return $this
     */
    public function where($field, $delimiter, $value=null);

    /**
     * Limit rows between two dates
     *
     * @param string|\DateTime|null $start  If null, leave starting period unlimited
     * @param string|\DateTime|null $end   If null, leave ending period unlimited
     * @return $this
     */
    public function between($start=null, $end=null);

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query();

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rows();

    /**
     * Count the number of rows
     *
     * @return int
     */
    public function count();

    /**
     * Return the sum of a column
     *
     * @return float
     */
    public function sum($column);
}