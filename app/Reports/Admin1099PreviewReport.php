<?php

namespace App\Reports;

use App\Admin\Queries\Caregiver1099Query;
use App\Caregiver1099;

class Admin1099PreviewReport
{
    /**
     * Get the results
     *
     * @param $filters
     * @return $this
     */
    public function applyFilters($filters)
    {
        $query = new Caregiver1099Query();
        $caregiver1099s = $query->generateReport($filters);

        return collect($caregiver1099s)->map(function($caregiver1099){

            return[
                'client_fname' => $caregiver1099->client_fname,
                'client_lname' => $caregiver1099->client_lname,
                'caregiver_fname' => $caregiver1099->caregiver_fname,
                'caregiver_lname' => $caregiver1099->caregiver_lname,
                'business_name' => $caregiver1099->business_name,
                'payment_total' => $caregiver1099->caregiver_1099_amount ? $caregiver1099->caregiver_1099_amount : $caregiver1099->payment_total,
                'caregiver_1099_amount' => $caregiver1099->caregiver_1099_amount,
                'caregiver_1099' => $caregiver1099->caregiver_1099,
                'caregiver_1099_id' => $caregiver1099->caregiver_1099_id,
                'caregiver_id' => $caregiver1099->caregiver_id,
                'client_id' => $caregiver1099->client_id,
                'errors' => Caregiver1099::getErrors($caregiver1099),
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
