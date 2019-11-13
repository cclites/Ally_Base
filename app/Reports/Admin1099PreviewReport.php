<?php

namespace App\Reports;

use App\Client;
use Illuminate\Database\Eloquent\Model;

class Admin1099PreviewReport extends BaseReport
{
    protected $threshold = 600;
    protected $report;
    protected $rawQuery;
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
     * Build a raw query
     *
     * @param string $year
     * @param int|null $businessId
     * @param int|null $clientId
     * @param int|null $caregiverId
     * @return $this
     */
    public function applyFilters(string $year, ?int $businessId, ?int $clientId, ?int $caregiverId, ?string $caregiver1099) : self
    {
        // Disable full group by mode
        \DB::statement('set session sql_mode=\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\';');

        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.

        $this->query = "SELECT c.id as client_id, u1.firstname as client_fname, u1.lastname as client_lname, u1.email as client_email, c.caregiver_1099 as caregiver_1099, 
b.id as business_id, b.name as business_name,
u2.id as caregiver_id, u2.firstname as caregiver_fname, u2.lastname as caregiver_lname, 
 sum(h.caregiver_shift) as payment_total
FROM clients c 
INNER JOIN shifts s ON s.client_id = c.id
INNER JOIN payments p ON s.payment_id = p.id
INNER JOIN shift_cost_history h ON h.id = s.id";

        $this->query .= " INNER JOIN users u1 ON u1.id = s.client_id";

        if($clientId){
            $this->query .= " AND u1.id = $clientId ";
        }

        $this->query .= " INNER JOIN users u2 ON u2.id = s.caregiver_id 
                    INNER JOIN caregivers c2 ON c2.id = u2.id";

        if($caregiverId){
            $this->query .= " AND c2.id = $caregiverId ";
        }

        if($caregiver1099 && $caregiver1099 !== 'no'){
            $this->query .= " AND c.caregiver_1099 = '$caregiver1099' ";
        }elseif ($caregiver1099 && $caregiver1099 === 'no' ){
            $this->query .= " AND c.caregiver_1099 is null ";
        }

        $this->query .= " INNER JOIN businesses b ON c.business_id = b.id ";

        if($businessId){
            $this->query .= " AND b.id = $businessId ";
        }

        $this->query .= " WHERE p.created_at BETWEEN '{$year}-01-01 00:00:00' AND '{$year}-12-31 23:59:59'
GROUP BY s.client_id, s.caregiver_id
HAVING payment_total > ?";

        return $this;
    }

    /**
     * Map the results
     *
     * @return iterable
     */
    protected function results(): iterable
    {
        return collect(\DB::select($this->query, [$this->threshold]))->map(function($row){

            return[
                'client_fname' => $row->client_fname,
                'client_lname' => $row->client_lname,
                'caregiver_fname' => $row->caregiver_fname,
                'caregiver_lname' => $row->caregiver_lname,
                'location' => $row->business_name,
                'total' => $row->payment_total,
                'caregiver_1099' => $row->caregiver_1099,
            ];
        });
    }
}
