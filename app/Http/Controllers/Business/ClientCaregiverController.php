<?php

namespace App\Http\Controllers\Business;

use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\ClientCaregiver;
use Illuminate\Http\Request;

class ClientCaregiverController extends BaseController
{
    public function store(Request $request, $client_id) {
        /**
         * @var \App\Client $client
         */
        $client = $this->business()->clients()->where('id', $client_id)->firstOrFail();

        $request->validate(['caregiver_id' => 'required|exists:caregivers,id']);
        $caregiver_id = $request->input('caregiver_id');

        $data = $request->validate([
            'caregiver_hourly_rate' => 'required|numeric',
            'caregiver_daily_rate' => 'nullable|numeric',
            'provider_hourly_fee' => 'required|numeric',
            'provider_daily_fee' => 'nullable|numeric',
        ]);

        // Force rates/fees to floats
        $data = array_map('floatval', $data);

        if ($client->caregivers()->syncWithoutDetaching([$caregiver_id => $data])) {
            $caregiver = $client->caregivers->where('id', $caregiver_id)->first();
            $responseData = new ClientCaregiver($client, $caregiver);
            return new CreatedResponse('The caregiver assignment has been saved.', $responseData->toResponse(null));
        }

        return new ErrorResponse(500, 'Unable to save caregiver assignment.');
    }

    public function index($client_id) {
        /**
         * @var \App\Client $client
         */
        $client = $this->business()->clients()->where('id', $client_id)->firstOrFail();

        $caregivers = $client->caregivers->map(function($caregiver) use ($client) {
            return (new ClientCaregiver($client, $caregiver))->toResponse(null);
        });
        return $caregivers;
    }
}
