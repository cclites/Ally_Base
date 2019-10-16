<?php

namespace App\Http\Controllers\Business;

use App\CareDetails;
use App\Http\Requests\UpdateClientCareDetailsRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Client;

class ClientCareDetailsController extends BaseController
{
    /**
     * Update the client care details.
     *
     * @param UpdateClientCareDetailsRequest $request
     * @param Client $client
     * @return SuccessResponse|ErrorResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateClientCareDetailsRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = CareDetails::convertFormData($request->validated());
        if ($client->careDetails()->updateOrCreate(['client_id' => $client->id], $data)) {
            return new SuccessResponse('Client care needs have been saved successfully.', $client->fresh()->careDetails);
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to save the client care needs.  Please try again.');
    }
}
