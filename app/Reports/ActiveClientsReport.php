<?php
namespace App\Reports;

use App\Client;

class ActiveClientsReport extends BaseReport
{
    public function __construct()
    {
        $this->query = Client::with('caregivers');
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
        $clients = $this->query()->get();
        return collect($clients);
    }
}