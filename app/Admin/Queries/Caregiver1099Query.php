<?php


namespace App\Admin\Queries;

use App\Admin\Queries\BaseQuery;
use App\Caregiver1099;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


class Caregiver1099Query
{
    protected $filters = [];
    protected $threshold = 600;

    /**
     * query is a function call to serve as a wrapper to make this
     * appear similar to operation to a normal query object.
     *
     * @param array $filters
     * @return array
     */
    public function generateReport(array $filters): array
    {
        $this->setFilters($filters);
        return $this->generateQuery();
    }

    function generateQuery(){
        \DB::statement('set session sql_mode=\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\';');

        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.

        $query = "SELECT c.id as client_id, 
                    u1.firstname as client_fname, 
                    u1.lastname as client_lname,
                    u1.email as client_email,  
                    c.client_type, 
                    c.ssn as client_ssn, 
                    c.caregiver_1099,
                    a1.address1 as client_address1, 
                    a1.address2 as client_address2,
                    a1.city as client_city,
                    a1.state as client_state,
                    a1.zip as client_zip,
                    b.id as business_id, 
                    b.name as business_name,
                    u2.id as caregiver_id, 
                    u2.firstname as caregiver_fname, 
                    u2.lastname as caregiver_lname,
                    u2.email as caregiver_email,
                    c2.ssn as caregiver_ssn,
                    c2.uses_ein_number,
                    a2.address1 as caregiver_address1, 
                    a2.address2 as caregiver_address2,
                    a2.city as caregiver_city,
                    a2.state as caregiver_state,
                    a2.zip as caregiver_zip,
                    ct.id as caregiver_1099_id,
                    ct.payment_total as caregiver_1099_amount,
                    ct.business_id as caregiver_1099_location,
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
                    WHERE p.created_at BETWEEN '" . $this->filters['year']['value'] ."-01-01 00:00:00' AND '" . $this->filters['year']['value'] ."-12-31 23:59:59'
                    AND c.business_id = " .  $this->filters['business_id']['value'] . " AND c.send_1099 = 'yes' ";


        if( array_key_exists('client_id', $this->filters) && filled($this->filters['client_id']['value'])){
            $query .= " AND u1.id = " . $this->filters['client_id']['value'];
        }


        if(array_key_exists('caregiver_id', $this->filters) && filled($this->filters['caregiver_id']['value'])){
            $query .= " AND c2.id =" .  $this->filters['caregiver_id']['value'];
        }

        if( array_key_exists('caregiver_1099', $this->filters) && filled($this->filters['caregiver_1099']['value'])){
            if( $this->filters['caregiver_1099']['value'] && $this->filters['caregiver_1099']['value'] !== 'no'){
                $query .= " AND c.caregiver_1099 = '" . (string)$this->filters['caregiver_1099']['value'] . "' ";
            }elseif ( $this->filters['caregiver_1099']['value'] && $this->filters['caregiver_1099']['value'] === 'no' ){
                $query .= " AND c.caregiver_1099 is null ";
            }
        }

        if( array_key_exists('transmitted', $this->filters) && filled($this->filters['transmitted']['value'])) {
            if ($this->filters['transmitted']['value'] && $this->filters['transmitted']['value'] === 1) {
                $query .= " AND ct.transmitted_at is not null ";
            } elseif ($this->filters['transmitted']['value'] && $this->filters['transmitted']['value']) {
                $query .= " AND ct.transmitted_at is null ";
            }
        }

        if( array_key_exists('created', $this->filters) && filled($this->filters['created']['value'])) {
            if ($this->filters['created']['value'] && $this->filters['created']['value'] === 1) {
                $query .= " AND ct.id is not null ";
            } elseif ($this->filters['created']['value'] && $this->filters['created']['value'] === 0) {
                $query .= " AND ct.id is null ";
            }
        }

        /*
        if( array_key_exists('report_type', $this->filters) && filled($this->filters['report_type']['value'])){
            $query .= " GROUP BY s.caregiver_id, s.business_id ";
        }else{
            $query .= " GROUP BY s.client_id, s.caregiver_id ";
        }
        */
        $query .= " GROUP BY s.client_id, s.caregiver_id ";
        $query .= "HAVING payment_total > ?";

        return \DB::select($query, [$this->threshold]);
    }

    /**
     * Set filters for query
     *
     * @param array|$filters
     * @return Model|void
     */
    public function setFilters(array $filters)
    {
        foreach ($filters as $filter=>$value){
            $this->filters[$filter] = [ 'name'=> $filter, 'value' => $value];
        }

    }

}