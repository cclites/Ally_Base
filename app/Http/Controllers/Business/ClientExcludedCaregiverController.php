<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\ClientExcludedCaregiver;
use App\Responses\ErrorResponse;
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
     * Exclude a Caregiver from a Client.
     *
     * @param ExcludeCaregiverRequest $request
     * @param \App\Client $client
     * @return ErrorResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ExcludeCaregiverRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();
        $data['client_id'] = $client->id;

        if ($caregiver = ClientExcludedCaregiver::create($data)) {
            return response()->json($caregiver);
        }

        return new ErrorResponse(500, 'Error excluding caregiver.');
    }

    /**
     * Update ExcludedCaregiver data.
     *
     * @param ExcludeCaregiverRequest $request
     * @param Client $client
     * @param ClientExcludedCaregiver $clientExcludedCaregiver
     * @return ErrorResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ExcludeCaregiverRequest $request, Client $client, ClientExcludedCaregiver $clientExcludedCaregiver)
    {
        $this->authorize('update', $client);

        $data = $request->filtered();
        $data['client_id'] = $client->id;
        
        $clientExcludedCaregiver->update($data);
        return response()->json($clientExcludedCaregiver);
    }
    
    /**
     * Remove the Caregiver exclusion.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
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
