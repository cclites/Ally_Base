<?php

namespace App\Reports;

use App\Admin\Queries\Caregiver1099Query;
use App\Caregiver1099;

class Ally1099PayerReport
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
                'caregiver_fname' => $caregiver1099->caregiver_fname,
                'caregiver_lname' => $caregiver1099->caregiver_lname,
                'business_name' => $caregiver1099->business_name,
                'payment_total' => $caregiver1099->caregiver_1099_amount ? $caregiver1099->caregiver_1099_amount : $caregiver1099->payment_total,
                'caregiver_id' => $caregiver1099->caregiver_id,
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
