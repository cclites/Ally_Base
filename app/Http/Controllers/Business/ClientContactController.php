<?php

namespace App\Http\Controllers\Business;

use App\ClientContact;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Client;
use App\Http\Requests\CreateClientContactRequest;

class ClientContactController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Client $client)
    {
        $this->authorize('read', $client);

        return response()->json($client->contacts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateClientContactRequest  $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateClientContactRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validated();

        if ($contact = $client->contacts()->create($data)) {
            return new SuccessResponse('New contact added successfully', $contact);
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CreateClientContactRequest  $request
     * @param \App\Client $client
     * @param  \App\ClientContact  $clientContact
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(CreateClientContactRequest $request, Client $client, ClientContact $clientContact)
    {
        $this->authorize('update', $client);

        $data = $request->validated();

        $clientContact->update($data);

        return new SuccessResponse('Contact updated successfully', $clientContact->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientContact  $clientContact
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Client $client, ClientContact $clientContact)
    {
        $this->authorize('update', $client);

        $clientContact->delete();

        return new SuccessResponse('Contact removed successfully', null);
    }
}
