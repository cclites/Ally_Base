<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\ClientExcludedCaregiver;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Http\Requests\ExcludeCaregiverRequest;

class ClientExcludedCaregiverController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Client $client)
    {
        $this->authorize('read', $client);

        return response()->json($client->excludedCaregivers->map(function($item) {
            $item->caregiver_name = $item->caregiver->name;
            return $item;
        }));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ExcludeCaregiverRequest $request
     * @param \App\Client $client
     * @return ErrorResponse|\Illuminate\Http\Response
     */
    public function store(ExcludeCaregiverRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();
        $data['client_id'] = $client->id;
        $caregiver = ClientExcludedCaregiver::create($data);

        if ($caregiver = ClientExcludedCaregiver::create($data)) {
            return response()->json($caregiver);
        }

        return new ErrorResponse(500, 'Error excluding caregiver.');
    }

    public function update(ExcludeCaregiverRequest $request, Client $client, ClientExcludedCaregiver $clientExcludedCaregiver)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();
        $data['client_id'] = $client->id;
        
        if ($clientExcludedCaregiver->update($data)) {
            return response()->json($clientExcludedCaregiver);
        };

        return new ErrorResponse(500, 'Error updating excluded caregiver.');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $excluded = ClientExcludedCaregiver::find($id);

        $client = $excluded->client;
        $this->authorize('update', $client);

        $excluded->delete();
        return response()->json([]);
    }
}
