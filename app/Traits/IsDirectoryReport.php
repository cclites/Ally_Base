<?php
namespace App\Traits;

trait IsDirectoryReport {
    
    /**
     * The list of columns to NOT include in the report
     *
     * @var array
     */
    protected $columns;

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
     * Count the number of rows
     *
     * @return int
     */
    public function count()
    {
        if ($this->rows) return $this->rows->count();
        return $this->query()->count();
    }

    /**
     * Save which columns to remove out of the final generated report
     *
     * @param array $params
     * @return void
     */
    public function applyColumnFilters($params)
    {
        foreach($params as $column => $shouldBePresent) {
            if(!$shouldBePresent) {
                $this->columns[] = $column;
            }
        }
    }

    /**
     * Remove columns that were set to be removed out of the final generated report
     *
     * @param \Illuminate\Support\Collection $rows
     * @return \Illuminate\Support\Collection
     */
    protected function filterColumns($rows)
    {
        return $rows->map(function($entry) {
            foreach ($this->columns as $column) {
                if(isset($entry[$column])) {
                    unset($entry[$column]);
                }
            }

            return $caregiver;
        });
    }
}
