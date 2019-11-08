<?php

namespace App\Reports;

use App\Client;
use Illuminate\Database\Eloquent\Model;

class Admin1099PreviewReport extends BaseReport
{
    protected $threshold = 600;
    protected $rows;

    public function __construct()
    {
        $this->query = Client::with([
            'shifts',
            'shifts.payment',
            'caregivers',
            'caregivers.addresses',
        ]);
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

    public function applyFilters(string $year, ?int $businessId, ?int $clientId, ?int $caregiverId) : self
    {
        \DB::statement('set session sql_mode=\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\';');

        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.

        $query = "SELECT c.id as client_id, CONCAT(u1.firstname, ' ', u1.lastname) as client_name, u1.email as client_email, c.client_type, c.default_payment_type, c.ssn as client_ssn, 
a1.address1 as client_address1, a1.address2 as client_address2, a1.city as client_city, a1.state as client_state, a1.zip as client_zip,
b.id as business_id, b.name as business_name,
u2.id as caregiver_id, CONCAT(u2.firstname, ' ', u2.lastname) as caregiver_name, u2.email as caregiver_email, c2.ssn as caregiver_ssn,
a2.address1 as caregiver_address1, a2.address2 as caregiver_address2, a2.city as caregiver_city, a2.state as caregiver_state, a2.zip as caregiver_zip,
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
WHERE p.created_at BETWEEN" . $year . "-01-01 00:00:00 AND " . $year . "-12-31 23:59:59 ";

        if($businessId){
            $query .= "AND c.business_id=" . $businessId . " ";
        }

        if($clientId){
            $query .= "AND c.id=" . $clientId . " ";
        }

        if($caregiverId){
            $query .= "AND c2.id=" . $caregiverId . " ";
        }

 $query .="GROUP BY s.client_id, s.caregiver_id
HAVING payment_total > ?";

        // Get rows
        $this->rows = \DB::select($query, [$this->threshold]);
    }

    protected function results()
    {
        // TODO: Implement results() method.
        \Log::info($this->rows);
    }
}
