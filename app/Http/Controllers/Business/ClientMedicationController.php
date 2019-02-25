<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Client;
use App\ClientMedication;
use App\Http\Requests\CreateClientMedicationRequest;
use App\Http\Requests\UpdateClientMedicationRequest;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;

class ClientMedicationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\CreateClientMedicationRequest  $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function store(CreateClientMedicationRequest $request, Client $client)
    {
        $this->authorize('update', $client);
        $data = $request->validated();
        $data['client_id'] = $client->id;

        if($medication = ClientMedication::create($data)) {
            return new SuccessResponse('Your medication was successfully added', $medication);
        }
        
        return new ErrorResponse(500, 'An error occured while trying to add your medication, please try again.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UpdateClientMedicationRequest  $request
     * @param  \App\ClientMedication  $medication
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientMedicationRequest $request, ClientMedication $medication)
    {
        $this->authorize('update', $medication->client);
        $data = $request->validated();

        if($medication->update($data)) {
            return new SuccessResponse('Your medication was successfully updated.', $medication->fresh());
        }
        
        return new ErrorResponse(500, 'An error occured while trying to update your medication. Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @param  \App\ClientMedication  $medication
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client, ClientMedication $medication)
    {
        $this->authorize('update', $client);
        
        if($medication->delete()) {
            return new SuccessResponse('This medication was deleted.');
        }
        
        return new ErrorResponse(500, 'An error occured while trying to delete this medication. Please refresh and try again.');
    }
}
