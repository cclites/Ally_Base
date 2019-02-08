<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;
use App\Billing\ClientRate;
use App\Client;
use App\Http\Requests\UpdateClientRatesRequest;
use App\Billing\Validators\ClientRateValidator;

class ClientRatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Client $client)
    {
        $this->authorize('read', $client);
        
        $data = $client->rates()->ordered()->get();

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateClientRatesRequest  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRatesRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();
        
        \DB::beginTransaction();
        try {
            if ($client->syncRates($data['rates'])) {
                $validator = new ClientRateValidator();
                if (! $validator->validate($client->fresh())) {
                    \DB::rollBack();
                    return new ErrorResponse(422, $validator->getErrorMessage());
                }

                \DB::commit();
                return new SuccessResponse('Client Rates saved successfully.', $client->fresh()->rates);
            } 

            throw new \Exception();
        } catch (\Exception $ex) {
            \Log::debug($ex->getMessage());
            \DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred.  Please try again.');
        }
    }
}
