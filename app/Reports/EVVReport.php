<?php


namespace App\Reports;

use App\Shift;

class EVVReport extends ShiftsReport
{

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * EVVReport constructor.
     */
    public function __construct()
    {
        $this->query = Shift::where('checked_in', 1)
            ->with(['client', 'caregiver', 'business']);
    }
    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        if (! $this->rows) {
            $this->rows = $this->query()->get();
        }

        return $this->rows;
    }
}