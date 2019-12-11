<?php


namespace App\Reports;

use App\Admin\Queries\Caregiver1099Query;
use App\Caregiver1099;
class Bad1099Report
{
    public function applyFilters($filters)
    {
        $query = new Caregiver1099Query();
        $caregiver1099s = $query->generateReport($filters);

        return collect($caregiver1099s)->map(function($caregiver1099){

            $errors = Caregiver1099::getErrors($caregiver1099);

            if(count($errors) > 0){
                return[
                    'caregiver' => $caregiver1099->caregiver_lname . ", " . $caregiver1099->caregiver_fname,
                    'client' => $caregiver1099->client_lname . ", " . $caregiver1099->client_fname,
                    'caregiver_id' => $caregiver1099->caregiver_id,
                    'client_id' => $caregiver1099->client_id,
                    'location' => $caregiver1099->business_name,
                    'errors' => implode(", ", $errors),
                ];
            }
        })->filter()
            ->values();
    }

}