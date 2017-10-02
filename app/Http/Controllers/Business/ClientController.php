<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ClientController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = $this->business()->clients()->with(['user', 'addresses', 'phoneNumbers'])->get();
        return view('business.clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('business.clients.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'nullable',
            'business_fee' => 'nullable|numeric',
        ]);

        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        $data['password'] = bcrypt(random_bytes(32));

        $client = new Client($data);
        if ($this->business()->clients()->save($client)) {
            return new CreatedResponse('The client has been created.', ['id' => $client->id]);
        }

        return new ErrorResponse(500, 'The client could not be created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $client->load(['user', 'addresses', 'phoneNumbers', 'bankAccounts', 'creditCards']);
        $schedules = $client->schedules()->get();
        $caregivers = $this->business()->caregivers->map(function($caregiver) {
            return [
                'id' => $caregiver->id,
                'firstname' => $caregiver->firstname,
                'lastname' => $caregiver->lastname,
                'default_rate' => $caregiver->pivot->default_rate,
            ];
        });

        return view('business.clients.show', compact('client', 'schedules', 'caregivers'));
    }

    public function edit(Client $client)
    {
        return $this->show($client);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'nullable|date',
            'business_fee' => 'nullable|numeric',
        ]);

        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);

        if ($client->update($data)) {
            return new CreatedResponse('The client has been updated.');
        }
        return new ErrorResponse(500, 'The client could not be updated.');
    }

    /**
     * Remove the specified client from the business.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        if ($client->delete()) {
            return new SuccessResponse('The client has been deleted.');
        }
        return new ErrorResponse('Could not delete the selected client.');
    }

    public function address(Request $request, $client_id, $type)
    {
        $client = Client::findOrFail($client_id);

        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        return (new AddressController())->update($request, $client->user, $type, 'The client\'s address');
    }

    public function phone(Request $request, $client_id, $type)
    {
        $client = Client::findOrFail($client_id);

        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        return (new PhoneController())->update($request, $client->user, $type, 'The client\'s phone number');
    }
}
