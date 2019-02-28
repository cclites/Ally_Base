<?php

namespace App\Http\Controllers\Clients;

use App\ClientContact;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Requests\CreateClientContactRequest;

class ClientContactController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('read', $this->client());

        return response()->json($this->client()->contacts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateClientContactRequest  $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateClientContactRequest $request)
    {
        $this->authorize('update', $this->client());

        $data = $request->filtered();

        if ($contact = $this->client()->contacts()->create($data)) {
            return new SuccessResponse('New contact added successfully', $this->client()->contacts->fresh());
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CreateClientContactRequest  $request
     * @param  \App\ClientContact  $clientContact
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(CreateClientContactRequest $request, ClientContact $clientContact)
    {
        $this->authorize('update', $this->client());

        $data = $request->filtered();

        $clientContact->update($data);

        return new SuccessResponse('Contact updated successfully', $this->client()->contacts->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientContact  $clientContact
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(ClientContact $clientContact)
    {
        $this->authorize('update', $this->client());

        $clientContact->delete();

        return new SuccessResponse('Contact removed successfully', $this->client()->contacts->fresh());
    }

    /**
     * Raise the emergency contact priority.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\ClientContact $clientContact
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function raisePriority(Request $request, ClientContact $clientContact)
    {
        $this->authorize('update', $this->client());

        $priority = $request->priority;
        if (empty($priority) || $priority < 1) {
            $priority = 1;
        }

        ClientContact::shiftPriorityDownAt($this->client()->id, $priority, $clientContact->id);

        $clientContact->update(['emergency_priority' => $priority]);

        return new SuccessResponse('Contact updated successfully', $this->client()->contacts->fresh());
    }
}
