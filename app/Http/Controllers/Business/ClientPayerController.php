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
    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateClientPayersRequest  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
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
            \Log::debug($ex->getMessage());
            \DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
        }
    }
}
