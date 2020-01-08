<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\UpdateClientPayersRequest;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;
use App\Billing\ClientPayer;
use App\Client;
use App\Billing\Validators\ClientPayerValidator;

class ClientPayerController extends Controller
{
    public function index(Client $client)
    {
        return $client->payers()->with('payer')->get();
    }

    public function uniquePayers(Client $client)
    {
        $query = ClientPayer::where('client_id', $client->id)->with('payer')->groupBy('payer_id')->select('payer_id');
        $results = $query->get();

        return $results->map(function(ClientPayer $clientPayer) use ($client) {
             return [
                 'id' => $clientPayer->payer_id,
                 'name' => $clientPayer->payer_id === 0 ? $client->name : $clientPayer->payer->name,
             ];
        });
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateClientPayersRequest $request
     * @param \App\Client $client
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function update(UpdateClientPayersRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();
        
        \DB::beginTransaction();
        try {
            if ($client->syncPayers($data['payers'])) {
                $validator = new ClientPayerValidator();
                if (! $validator->validate($client->fresh())) {
                    \DB::rollBack();
                    return new ErrorResponse(422, $validator->getErrorMessage());
                }

                \DB::commit();
                return new SuccessResponse('Client Payers saved successfully.', $client->fresh()->payers);
            } 

            throw new \Exception();
        } catch (\Exception $ex) {
            // This means the call to syncPayers threw an exception, and was already
            // captured there.  No need to log to sentry again, besides this is an
            // empty exception as thrown above.
            \DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
        }
    }
}
