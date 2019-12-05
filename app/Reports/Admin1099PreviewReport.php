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
    protected $threshold = 600;


    public function __construct(int $year, int $business_id, ?int $client_id, ?int  $caregiver_id, ?string $caregiver_1099, ?int $created, ?int $transmitted, ?int $caregiver_1099_id)
    {
        /*
        \Log::info("_construct parameters");
        \Log::info(json_encode($this));
        \Log::info("*************************************************\n");
        */


        $this->year = $year;
        $this->business_id = $business_id;
        $this->client_id = $client_id;
        $this->caregiver_id = $caregiver_id;
        $this->caregiver_1099 = $caregiver_1099;
        $this->created = $created;
        $this->transmitted = $transmitted;
        $this->caregiver_1099_id = $caregiver_1099_id;

        $this->applyFilters();
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
    public function applyFilters()
    {
        \DB::statement('set session sql_mode=\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\';');

        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.

        $query = "SELECT c.id as client_id, 
                    u1.firstname as client_fname, 
                    u1.lastname as client_lname,  
                    c.client_type, 
                    c.ssn as client_ssn, 
                    c.caregiver_1099,
                    a1.address1 as client_address1, 
                    a1.address2 as client_address2,
                    CONCAT(a1.city, ',  ', a1.state, ' ', a1.zip) as client_address3,
                    b.id as business_id, 
                    b.name as business_name,
                    u2.id as caregiver_id, 
                    u2.firstname as caregiver_fname, 
                    u2.lastname as caregiver_lname, 
                    c2.ssn as caregiver_ssn,
                    a2.address1 as caregiver_address1, 
                    a2.address2 as caregiver_address2,
                    CONCAT(a2.city, ',  ', a2.state, ' ', a2.zip) as caregiver_address3,
                    ct.id as caregiver_1099_id,
                    ct.transmitted_at,
                    ct.payment_total as caregiver_1099_amount,
                    sum(h.caregiver_shift) as payment_total
                    FROM clients c
                    INNER JOIN shifts s ON s.client_id = c.id
                    INNER JOIN payments p ON s.payment_id = p.id
                    INNER JOIN shift_cost_history h ON h.id = s.id
                    INNER JOIN users u1 ON u1.id = s.client_id
                    INNER JOIN users u2 ON u2.id = s.caregiver_id
                    INNER JOIN caregivers c2 ON c2.id = u2.id
                    INNER JOIN businesses b ON c.business_id = b.id
                    LEFT JOIN addresses a1 ON a1.id = (SELECT id FROM addresses WHERE user_id = u1.id ORDER BY `type` LIMIT 1)
                    LEFT JOIN addresses a2 ON a2.id = (SELECT id FROM addresses WHERE user_id = u2.id ORDER BY `type` LIMIT 1)
                    LEFT JOIN caregiver_1099s ct on ct.client_id = c.id AND ct.caregiver_id = c2.id
                    WHERE p.created_at BETWEEN '" . $this->year ."-01-01 00:00:00' AND '" . $this->year ."-12-31 23:59:59'
                    AND c.business_id = " .  $this->business_id;

        if($this->client_id){
            $query .= " AND u1.id = " . $this->client_id;
        }

        if($this->caregiver_id){
            $query .= " AND c2.id =" .  $this->caregiver_id;
        }

        if($this->caregiver_1099 && $this->caregiver_1099 !== 'no'){
            $query .= " AND c.caregiver_1099 = '" . (string)$this->caregiver_1099;
        }elseif($this->caregiver_1099 && $this->caregiver_1099 === 'no'){
            $query .= " AND c.caregiver_1099 is null ";
        }

        if($this->transmitted && $this->transmitted === 1){
            $query .= " AND ct.transmitted_at is not null ";
        }elseif($this->transmitted && $this->transmitted === 0){
            $query .= " AND ct.transmitted_at is null ";
        }

        if($this->created && $this->created === 1){
            $query .= " AND ct.id is not null ";
        }elseif($this->created && $this->created === 0){
            $query .= " AND ct.id is null ";
        }

        $query .= " GROUP BY s.client_id, s.caregiver_id
                              HAVING payment_total > ?";

        $this->report = \DB::select($query, [$this->threshold]) ;

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
