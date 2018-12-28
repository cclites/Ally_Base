<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Http\Requests\CreateClientRequest;
use App\Http\Requests\UpdateClientPreferencesRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Mail\ClientConfirmation;
use App\OnboardStatusHistory;
use App\Responses\ConfirmationResponse;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Shifts\AllyFeeCalculator;
use App\Traits\Request\PaymentMethodRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientController extends BaseController
{
    use PaymentMethodRequest;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = Client::forRequestedBusinesses()
                ->when($request->filled('client_type'), function($query) use ($request) {
                    $query->where('client_type', $request->input('client_type'));
                })
                ->ordered();

            // Default to active only, unless active is provided in the query string
            if ($request->input('active', 1) !== null) {
                $query->where('active', $request->input('active', 1));
            }
            // Use query string ?address=1&phone_number=1&care_plans=1 if data is needed
            if ($request->input('address')) {
                $query->with('address');
            }
            if ($request->input('phone_number')) {
                $query->with('phoneNumber');
            }
            if ($request->input('care_plans')) {
                $query->with('carePlans');
            }

            $clients = $query->with('caseManager')->get();
            return $clients;
        }

        return view('business.clients.index');
    }

    public function listNames()
    {
        $query = Client::forRequestedBusinesses();

        if (request()->care_plans) {
            $query->with('carePlans');
        }

        return $query->whereHas('user', function ($q) {
            $q->where('active', true);
        })
            ->with(['user'])->get()->map(function($client) {
                return [
                    'id' => $client->id,
                    'firstname' => $client->user->firstname,
                    'lastname' => $client->user->lastname,
                    'name' => $client->nameLastFirst(),
                    'nameLastFirst' => $client->nameLastFirst(),
                    'care_plans' => (request()->care_plans) ? $client->carePlans : null,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
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
     * @param \App\Http\Requests\CreateClientRequest $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(CreateClientRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [Client::class, $data]);

        // Look for duplicates
        if (!$request->override) {
            if ($request->email && Client::forRequestedBusinesses()->whereEmail($request->email)->first()) {
                return new ConfirmationResponse('There is already a client with the email address ' . $request->email . '.');
            }
            if (Client::forRequestedBusinesses()->whereName($request->firstname, $request->lastname)->first()) {
                return new ConfirmationResponse('There is already a client with the name ' . $request->firstname . ' ' . $request->lastname . '.');
            }
        }
        $data['created_by'] = auth()->id();
        if ($client = Client::create($data)) {
            if ($request->input('no_email')) {
                $client->setAutoEmail()->save();
            }

            $history = new OnboardStatusHistory([
                'status' => $data['onboard_status']
            ]);
            $client->onboardStatusHistory()->save($history);

            // Provider pay
            if ($request->provider_pay) {
                $client->setPaymentMethod($client->business);
            }

            return new CreatedResponse('The client has been created.', [ 'id' => $client->id, 'url' => route('business.clients.edit', [$client->id]) ]);
        }

        return new ErrorResponse(500, 'The client could not be created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client  $client
     * @return ErrorResponse|\Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        $this->authorize('read', $client);

        $client->load([
            'user',
            'creator',
            'updator',
            'addresses',
            'phoneNumbers',
            'preferences',
            'bankAccounts',
            'creditCards',
            'payments',
            'user.documents',
            'meta',
            'notes.creator',
            'careDetails',
            'carePlans',
            'caseManager',
            'notes' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            },
        ]);
        $client->allyFee = AllyFeeCalculator::getPercentage($client);
        $client->hasSsn = (strlen($client->ssn) == 11);

        // include a placeholder for the primary number if one doesn't already exist
        if ($client->phoneNumbers->where('type', 'primary')->count() == 0) {
            $client->phoneNumbers->prepend(['type' => 'primary', 'extension' => '', 'number' => '']);
        }

        // include a placeholder for the billing number if one doesn't already exist
        if ($client->phoneNumbers->where('type', 'billing')->count() == 0) {
            $client->phoneNumbers->prepend(['type' => 'billing', 'extension' => '', 'number' => '']);
        }

        // append payment metrics and future schedule count
        if (!empty($client->default_payment_id)) {
            $client->defaultPayment->charge_metrics = $client->defaultPayment->charge_metrics;
        }
        if (!empty($client->backup_payment_id)) {
            $client->backupPayment->charge_metrics = $client->backupPayment->charge_metrics;
        }
        $client->future_schedules = $client->futureSchedules()->count();

        $lastStatusDate = $client->onboardStatusHistory()->orderBy('created_at', 'DESC')->value('created_at');
        $business = $this->business();

        return view('business.clients.show', compact('client', 'caregivers', 'lastStatusDate', 'business'));
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
    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);
        $data = $request->filtered();
        $data['updated_by'] = auth()->id();

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

            return new SuccessResponse('The client has been updated.', $client);
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
        $this->authorize('delete', $client);

        if ($client->hasActiveShift()) {
            return new ErrorResponse(400, 'You cannot delete this client because they have an active shift clocked in.');
        }

        try {
            $inactive_at = request('inactive_at') ? Carbon::parse(request('inactive_at')) : Carbon::now();
        } catch (\Exception $ex) {
            return new ErrorResponse(422, 'Invalid inactive date.');
        }

        if ($client->update(['active' => false, 'inactive_at' => $inactive_at])) {
            $client->clearFutureSchedules();
            return new SuccessResponse('The client has been archived.', [], route('business.clients.index'));
        }
        return new ErrorResponse('Could not archive the selected client.');
    }

    /**
     * Re-activate an archived (inactive) client.  This reverses the destroy action above.
     *
     * @param \App\Client $client
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function reactivate(Client $client)
    {
        $this->authorize('update', $client);

        if ($client->update(['active' => true, 'inactive_at' => null])) {
            $client->clearFutureSchedules();
            return new SuccessResponse('The client has been re-activated.');
        }
        return new ErrorResponse('Could not re-activate the selected client.');
    }

    /**
     * Updates relating to the service orders tab
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function serviceOrders(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'max_weekly_hours' => 'required|numeric|min:0|max:999',
        ]);

        if ($client->update($data)) {
            return new SuccessResponse('The service orders have been updated');
        }
        return new ErrorResponse(500, 'Unable to update service orders.');
    }

    public function address(Request $request, Client $client, $type)
    {
        $this->authorize('update', $client);

        return (new AddressController())->update($request, $client->user, $type, 'The client\'s address');
    }

    public function phone(Request $request, Client $client, $type)
    {
        $this->authorize('update', $client);

        return (new PhoneController())->upsert($request, $client->user, $type, 'The client\'s phone number');
    }

    public function paymentMethod(Request $request, Client $client, string $type)
    {
        $this->authorize('update', $client);

        $backup = ($type === 'backup');

        if ($request->input('use_business')) {
            if (!$client->business->paymentAccount) return new ErrorResponse(400, 'There is no provider payment account on file.');
            if ($client->setPaymentMethod($client->business, $backup)) {
                return $this->paymentMethodResponse($client, 'The payment method has been set to the provider payment account.');
            }
            return new ErrorResponse(500, 'The payment method could not be updated.');
        }

        $method = $this->validatePaymentMethod($request, $client->getPaymentMethod($backup));
        if ($client->setPaymentMethod($method, $backup)) {
            return $this->paymentMethodResponse($client, 'The payment method has been updated.');
        }
        return new ErrorResponse(500, 'The payment method could not be updated.');
    }

    public function destroyPaymentMethod(Client $client, string $type)
    {
        $this->authorize('update', $client);

        if ($type == 'backup') {
            $client->backupPayment()->dissociate();
        }
        else {
            $client->defaultPayment()->dissociate();
        }
        $client->save();

        return $this->paymentMethodResponse($client, 'The payment method has been removed.');
    }

    protected function paymentMethodResponse(Client $client, $message)
    {
        $allyRate = $client->getAllyPercentage();
        $paymentTypeMessage = "Active Payment Type: " . $client->getPaymentType() . " (" . round($allyRate * 100, 2) . "% Processing Fee)";
        $data['payment_text'] = $paymentTypeMessage;
        $data['ally_rate'] = $allyRate;
        return new SuccessResponse($message, $data);
    }

    public function sendConfirmationEmail(Client $client)
    {
        $this->authorize('update', $client);

        $client->sendConfirmationEmail();
        return new SuccessResponse('Email Sent to Client');
    }

    public function getPaymentType(Client $client)
    {
        return [
            'payment_type' => $client->getPaymentType(),
            'percentage_fee' => AllyFeeCalculator::getPercentage($client)
        ];
    }

    public function changePassword(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        if ($client->user->changePassword($request->input('password'))) {
            return new SuccessResponse('The client\'s password has been updated.');
        }
        return new ErrorResponse(500, 'Unable to update client password.');
    }

    public function ltci(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->only([
            'ltci_name',
            'ltci_address',
            'ltci_city',
            'ltci_state',
            'ltci_zip',
            'ltci_policy',
            'ltci_claim',
            'ltci_phone',
            'ltci_fax',
            'medicaid_id',
            'medicaid_diagnosis_codes'
        ]);

        if($client->update($data)) {
            return new SuccessResponse('Client info updated.');
        } else {
            return new ErrorResponse(500, 'Error updating client info.');
        }
    }

    public function preferences(UpdateClientPreferencesRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->setPreferences($request->validated());

        return new SuccessResponse('Client preferences updated.');
    }

    public function defaultRates(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'hourly_rate_id' => 'nullable|exists:rate_codes,id',
            'fixed_rate_id' => 'nullable|exists:rate_codes,id',
        ]);

        $client->update($data);
        return new SuccessResponse('The default rates have been saved.');
    }
}
