<?php
namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Responses\ErrorResponse;
use App\Responses\Resources\CaregiverClient;

class CaregiverClientController extends BaseController
{
    public function index(Caregiver $caregiver)
    {
        $this->authorize('read', $caregiver);

        $caregivers = $caregiver->clients->map(function ($client) use ($caregiver) {
            return (new CaregiverClient($client, $caregiver))->toResponse(null);
        });

        return $caregivers->sortBy('name')->values()->all();
    }
}