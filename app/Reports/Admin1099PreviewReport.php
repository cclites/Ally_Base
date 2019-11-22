<?php

namespace App\Reports;

use App\Caregiver1099;
use App\Client;
use Illuminate\Database\Eloquent\Model;

class Admin1099PreviewReport extends BaseReport
{

    protected $report;
    protected $year;
    protected $business_id;
    protected $client_id;
    protected $caregiver_id;
    protected $caregiver_1099;
    protected $created;
    protected $transmitted;
    protected $caregiver_1099_id;

    public function __construct($year, $business_id, $client_id, $caregiver_id, $caregiver_1099, $created, $transmitted, $caregiver_1099_id)
    {
        $this->year = $year;
        $this->business_id = $business_id;
        $this->client_id = $client_id;
        $this->caregiver_id = $caregiver_id;
        $this->caregiver_1099 = $caregiver_1099;
        $this->created = $created;
        $this->transmitted = $transmitted;
        $this->caregiver_1099_id = $caregiver_1099_id;

        return $this->applyFilters();
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
    public function applyFilters() : iterable
    {
        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.
        $rows = new Caregiver1099([$this->year, $this->business_id, $this->client_id, $this->caregiver_id, $this->caregiver_1099, $this->created, $this->transmitted]);


        $this->rows = collect($rows)->map(function($row){

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
                'id' => $row->caregiver_1099_id,
            ];

        })->values();
    }

    /**
     * Map the results
     *
     * @return iterable
     */
    protected function results(): iterable
    {
    }

    public function report(){
        return $this->report;
    }
}
