<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\ClientExcludedCaregiver;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ClientExcludedCaregiverController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        return response()->json($client->excludedCaregivers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ErrorResponse|\Illuminate\Http\Response
     */
    public function store(Request $request, $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $data = $request->validate(['caregiver_id' => 'required|int']);

        $caregiver = ClientExcludedCaregiver::create([
            'client_id' => $client,
            'caregiver_id' => $data['caregiver_id']
        ]);

        if ($caregiver) {
            return response()->json($caregiver);
        }

        return new ErrorResponse(500, 'Error excluding caregiver.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientExcludedCaregiver  $clientExcludedCaregiver
     * @return \Illuminate\Http\Response
     */
    public function show(ClientExcludedCaregiver $clientExcludedCaregiver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientExcludedCaregiver  $clientExcludedCaregiver
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientExcludedCaregiver $clientExcludedCaregiver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientExcludedCaregiver  $clientExcludedCaregiver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientExcludedCaregiver $clientExcludedCaregiver)
    {
        //
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

        if (!$this->businessHasClient($excluded->client_id)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $excluded->delete();
        return response()->json([]);
    }
}
