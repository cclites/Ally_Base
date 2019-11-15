<?php

namespace App\Reports;

use App\Caregiver1099;
use App\Client;
use Illuminate\Database\Eloquent\Model;

class Admin1099PreviewReport extends BaseReport
{
    protected $threshold = 600;
    protected $report;
    protected $rows;
    protected $query;

    public function __construct()
    {
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query() : self
    {
        return $this->query;
    }

    /**
     * Get the results
     *
     * @param string $year
     * @param int|null $businessId
     * @param int|null $clientId
     * @param int|null $caregiverId
     * @return $this
     */
    public function applyFilters(string $year, int $businessId, ?int $clientId, ?int $caregiverId, ?string $caregiver1099, ?string $created, ?string $transmitted) : iterable
    {
        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.
        $caregiver1099s = new Caregiver1099();
        $rows = $caregiver1099s->generateQuery($year, $businessId, $clientId, $caregiverId, $caregiver1099, $created, $transmitted);

        return collect($rows)->map(function($row){

            return[
                'client_fname' => $row->client_fname,
                'client_lname' => $row->client_lname,
                'caregiver_fname' => $row->caregiver_fname,
                'caregiver_lname' => $row->caregiver_lname,
                'location' => $row->business_name,
                'total' => $row->payment_total,
                'caregiver_1099' => $row->caregiver_1099,
                'caregiver_1099_id' => $row->caregiver_1099_id,
                'caregiver_id' => $row->caregiver_id,
                'client_id' => $row->client_id,
                'transmitted' => $row->transmitted_at,
            ];

        });
    }

    /**
     * Map the results
     *
     * @return iterable
     */
    protected function results(): iterable
    {
    }
}
