<?php


namespace App\Http\Controllers\Business;


use App\Caregiver;

class CaregiverClientController
{
    public function index(Caregiver $caregiver)
    {
        if (!$this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $caregivers = $caregiver->clients->map(function ($client) use ($caregiver) {
            return (new ClientCaregiver($client, $caregiver))->toResponse(null);
        });

        return $caregivers->sortBy('name')->values()->all();
    }
}