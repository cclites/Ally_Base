<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\CreateClientPayerRequest;
use App\Http\Requests\UpdateClientPayerRequest;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;
use App\Billing\ClientPayer;
use App\Client;

class ClientPayerController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateClientPayerRequest  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function store(CreateClientPayerRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();

        if ($payer = $client->payers()->create($data)) {
            return new SuccessResponse('Payer added to client successfully.', $payer->fresh()->load('payer'));
        }

        return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateClientPayerRequest  $request
     * @param  \App\Client  $client
     * @param  \App\Billing\ClientPayer  $payer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientPayerRequest $request, Client $client, ClientPayer $payer)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();

        if ($payer->update($data)) {
            return new SuccessResponse('Client Payer details updated successfully.', $payer->fresh());
        }

        return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @param  \App\Billing\ClientPayer  $payer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client, ClientPayer $payer)
    {
        $this->authorize('update', $client);

        try {
            if ($payer->delete()) {
                ClientPayer::shiftPriorityUpAt($client->id, $payer->priority);
                return new SuccessResponse('Payer was successfully removed from the Client.', $payer);
            }
        } catch (\Exception $ex) {
            logger($e->getMessage());
        }

        return new ErrorResponse(500, 'Client Payer could not be removed.');
    }

    /**
     * Update the ClientPayer's priority.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @param \App\Billing\ClientPayer $payer
     * @return \Illuminate\Http\Response
     */
    public function updatePriority(Request $request, Client $client, ClientPayer $payer)
    {
        $this->authorize('update', $client);

        $request->validate(['priority' => 'required|numeric|between:0,999']);

        ClientPayer::shiftPriorityDownAt($client->id, $request->priority, $payer->id);
        
        $payer->update(['priority' => $request->priority]);

        return response()->json($client->fresh()->payers);
    }
}
