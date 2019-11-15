<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Caregiver1099 extends Model
{
    protected $query;
    protected $threshold = 600;
    protected $rows;

    // Relations
    public function caregiver(){
        return belongsTo(Caregiver::class);
    }


    // Instance Methods
    public function generateQuery(string $year, int $businessId, ?int $clientId, ?int $caregiverId, ?string $caregiver1099, ?int $created, ?int $transmitted)
    {
        \DB::statement('set session sql_mode=\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\';');

        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.
        $year = (int)$year;
        $businessId = (int)$businessId;
        $threshold = 600;

        $query = "SELECT c.id as client_id, 
                    u1.firstname as client_fname, 
                    u1.lastname as client_lname,  
                    c.client_type, 
                    c.ssn as client_ssn, 
                    c.caregiver_1099,
                    a1.address1 as client_address1, 
                    a1.address2 as client_address2,
                    CONCAT(a1.city, ', ', a1.state, ' ', a1.zip) as client_address3,
                    b.id as business_id, 
                    b.name as business_name,
                    u2.id as caregiver_id, 
                    u2.firstname as caregiver_fname, 
                    u2.lastname as caregiver_lname, 
                    c2.ssn as caregiver_ssn,
                    a2.address1 as caregiver_address1, 
                    a2.address2 as caregiver_address2,
                    CONCAT(a2.city, ',', a2.state, ' ', a2.zip) as caregiver_address3,
                    ct.id as caregiver_1099_id,
                    ct.transmitted_at,
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
                    WHERE p.created_at BETWEEN '$year-01-01 00:00:00' AND '$year-12-31 23:59:59'
                    AND c.business_id = $businessId ";

                    if($clientId){
                        $query .= " AND u1.id = " . (int)$clientId;
                    }

                    if($caregiverId){
                        $query .= " AND c2.id =" .  (int)$caregiverId;
                    }

                    if($caregiver1099 && $caregiver1099 !== 'no'){
                        $query .= " AND c.caregiver_1099 = '" . (string)$caregiver1099 . "' ";
                    }elseif ($caregiver1099 && $caregiver1099 === 'no' ){
                        $query .= " AND c.caregiver_1099 is null ";
                    }

                    if($transmitted && $transmitted === 1){
                        $query .= " AND ct.transmitted_at is not null ";
                    }elseif($transmitted && $transmitted === 0){
                        $query .= " AND ct.transmitted_at is null ";
                    }

                    if($created && $created === 1){
                        $query .= " AND ct.id is not null ";
                    }elseif($created && $created === 0){
                        $query .= " AND ct.id is null ";
                    }

                    $query .= " GROUP BY s.client_id, s.caregiver_id
                              HAVING payment_total > ?";

        // Get rows
        return \DB::select($query, [$threshold]);
    }
}
