<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\CreateClientMedicationRequest;
use App\Http\Requests\UpdateClientMedicationRequest;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\ClientMedication;
use App\Client;

class ClientMedicationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateClientMedicationRequest $request
     * @param \App\Client $client
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateClientMedicationRequest $request, Client $client)
    {
        $this->authorize('update', $client);
        $data = $request->validated();
        $data['client_id'] = $client->id;

        if ($medication = ClientMedication::create($data)) {
            return new SuccessResponse('Client medication was successfully added', $medication);
        }

        return new ErrorResponse(500, 'An error occurred while trying to add client medication, please try again.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateClientMedicationRequest $request
     * @param \App\ClientMedication $medication
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateClientMedicationRequest $request, ClientMedication $medication)
    {
        $this->authorize('update', $medication->client);
        $data = $request->validated();

        if ($medication->update($data)) {
            return new SuccessResponse('Client medication was successfully updated.', $medication->fresh());
        }

        return new ErrorResponse(500, 'An error occurred while trying to update client medication. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Client $client
     * @param \App\ClientMedication $medication
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Client $client, ClientMedication $medication)
    {
        $this->authorize('update', $client);

        if ($medication->delete()) {
            return new SuccessResponse('Client medication was deleted.');
        }

        return new ErrorResponse(500, 'An error occurred while trying to delete client medication. Please refresh and try again.');
    }
}
