<?php


namespace App\Reports;


class AvailableShiftReports extends BaseReport
{

    protected $startDate;

    protected $endDate;

    protected $client;

    protected $city;

    protected $service;

    protected $query;

    /**
     * @inheritDoc
     */
    public function query()
    {
        return $this->query;
    }

    public function applyFilters(){}

    /**
     * @inheritDoc
     */
    protected function results()
    {
        // TODO: Implement results() method.
    }
}