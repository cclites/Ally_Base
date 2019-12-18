<?php

namespace App\Reports;


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
        return [];
//        $query = new Caregiver1099Query();
//        $caregiver1099s = $query->generateReport($filters);
//
//        return collect($caregiver1099s)->map(function($caregiver1099){
//
//            return[
//                'caregiver_first_name' => $caregiver1099->caregiver_first_name,
//                'caregiver_last_name' => $caregiver1099->caregiver_last_name,
//                'business_name' => $caregiver1099->business_name,
//                'payment_total' => $caregiver1099->caregiver_1099_amount ? $caregiver1099->caregiver_1099_amount : $caregiver1099->payment_total,
//                'caregiver_id' => $caregiver1099->caregiver_id,
//            ];
//
//        });

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
