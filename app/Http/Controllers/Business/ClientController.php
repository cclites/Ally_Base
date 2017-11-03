<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PhoneController;
use App\Mail\ClientReconfirmation;
use App\OnboardStatusHistory;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidSSN;
use App\Scheduling\AllyFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function listNames()
    {
        return $this->business()->clients()->with(['user'])->get()->map(function($client) {
            return [
                'id' => $client->id,
                'firstname' => $client->user->firstname,
                'lastname' => $client->user->lastname,
                'name' => $client->nameLastFirst(),
            ];
        });
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
        $data = $request->validate(
            [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users',
                'date_of_birth' => 'nullable',
                'business_fee' => 'nullable|numeric',
                'client_type' => 'required',
                'ssn' => ['nullable', new ValidSSN()],
                'onboard_status' => 'required',
            ]
        );

        if (substr($data['ssn'], 0, 3) == '***') unset($data['ssn']);
        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        $data['password'] = bcrypt(random_bytes(32));

        $client = new Client($data);
        if ($this->business()->clients()->save($client)) {
            $history = new OnboardStatusHistory([
                'status' => $data['onboard_status']
            ]);
            $client->onboardStatusHistory()->save($history);
            return new CreatedResponse('The client has been created.', ['id' => $client->id], route('business.clients.edit', [$client->id]));
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

        $client->load(['user', 'addresses', 'phoneNumbers', 'bankAccounts', 'creditCards', 'user.documents']);
        $schedules = $client->schedules()->get();
        $caregivers = $this->business()->caregivers()
              ->with('user')
              ->where('business_id', $this->business()->id)
              ->get()
              ->sortBy('user.lastname')
              ->map(function($caregiver) {
                  return ['id' => $caregiver->id, 'name' => $caregiver->nameLastFirst(), 'default_rate' => $caregiver->default_rate];
              });

        $client->hasSsn = (strlen($client->ssn) == 11);
        $lastStatusDate = $client->onboardStatusHistory()->orderBy('created_at', 'DESC')->value('created_at');
        $business = $client->business;

        return view('business.clients.show', compact('client', 'schedules', 'caregivers', 'lastStatusDate', 'business'));
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
            'email' => ['required', 'email', Rule::unique('users')->ignore($client->id)],
            'date_of_birth' => 'nullable|date',
            'business_fee' => 'nullable|numeric',
            'client_type' => 'required',
            'ssn' => ['nullable', new ValidSSN()],
            'onboard_status' => 'required',
        ]);

        if (substr($data['ssn'], 0, 3) == '***') unset($data['ssn']);
        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);

        $addOnboardRecord = false;
        if ($client->onboard_status != $data['onboard_status']) {
            $addOnboardRecord = true;
        }

        if ($client->update($data)) {
            if ($addOnboardRecord) {
                $history = new OnboardStatusHistory([
                    'status' => $data['onboard_status']
                ]);
                $client->onboardStatusHistory()->save($history);
            }

            return new SuccessResponse('The client has been updated.');
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

        if ($client->hasActiveShift()) {
            return new ErrorResponse(400, 'You cannot delete this client because they have an active shift clocked in.');
        }

        if ($client->delete()) {
            $client->clearFutureSchedules();
            return new SuccessResponse('The client has been archived.', [], route('business.clients.index'));
        }
        return new ErrorResponse('Could not archive the selected client.');
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

    public function paymentMethod(Request $request, $client_id, $type)
    {
        $client = Client::findOrFail($client_id);

        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        return (new PaymentMethodController())->update($request, $client, $type, 'The client\'s payment method');
    }

    public function sendConfirmationEmail($client_id)
    {
        $client = Client::findOrFail($client_id);
        $status = 'emailed_reconfirmation';

        \Mail::to($client)->send(new ClientReconfirmation($client, $this->business()));

        $client->update(['onboard_status' => $status]);
        $history = new OnboardStatusHistory(compact('status'));
        $client->onboardStatusHistory()->save($history);

        return new SuccessResponse('Email Sent to Client');
    }

    public function getAllyPercentage($client_id)
    {
        $client = Client::findOrFail($client_id);
        return ['percentage' => AllyFeeCalculator::getPercentage($client, $client->defaultPayment)];
    }
}
