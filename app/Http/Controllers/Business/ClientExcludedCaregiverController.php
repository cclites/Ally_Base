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
     * @param  \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @return ErrorResponse|\Illuminate\Http\Response
     */
    public function store(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'caregiver_id' => 'required|int',
            'effective_at' => 'nullable|date',
        ]);

        $caregiver = ClientExcludedCaregiver::create([
            'client_id' => $client->id,
            'caregiver_id' => $data['caregiver_id'],
            'note' => $request->input('note', null),
            'reason' => $request->input('reason', null),
            'effective_at' => filter_date($request->effective_at),
        ]);

        if ($caregiver) {
            return response()->json($caregiver);
        }

        return new ErrorResponse(500, 'Error excluding caregiver.');
    }

    public function update(Request $request, Client $client, ClientExcludedCaregiver $clientExcludedCaregiver)
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'caregiver_id' => 'required|int',
            'effective_at' => 'nullable|date',
        ]);

        $clientExcludedCaregiver->update([
            'client_id' => $client->id,
            'note' => $request->input('note', null),
            'reason' => $request->input('reason', null),
            'effective_at' => filter_date($request->effective_at),
        ]);

        return response()->json($clientExcludedCaregiver);
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
