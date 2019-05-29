<?php

namespace App\Http\Controllers\Business;

use App\Actions\CreateClient;
use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\Client;
use App\ClientEthnicityPreference;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Http\Requests\CreateClientRequest;
use App\Http\Requests\UpdateClientPreferencesRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Responses\ConfirmationResponse;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\SalesPerson;
use App\Shifts\AllyFeeCalculator;
use App\Billing\Service;
use App\Billing\Payer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Notifications\ClientWelcomeEmail;
use App\Notifications\TrainingEmail;

class ClientController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = Client::forRequestedBusinesses()->ordered();

            // Default to active only, unless active is provided in the query string
            if ($request->input('active', 1) !== null) {
                $query->where('active', $request->input('active', 1));
            }
            if ($request->input('status') !== null) {
                $query->where('status_alias_id', $request->input('status', null));
            }
            if ($clientType = $request->input('client_type')) {
                $query->where('client_type', $clientType);
            }
            if ($caseManagerId = $request->input('case_manager_id')) {
                $query->whereHas('caseManager', function ($q) use ($caseManagerId) {
                    $q->where('id', $caseManagerId);
                });
            }
            // Use query string ?address=1&phone_number=1&care_plans=1&case_managers=1 if data is needed
            if ($request->input('address')) {
                $query->with('address');
            }
            if ($request->input('phone_number')) {
                $query->with('phoneNumber');
            }
            if ($request->input('care_plans')) {
                $query->with('carePlans');
            }
            if ($request->input('case_managers')) {
                $query->with('caseManager');
            }

            $clients = $query->get();
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
            ->with(['user'])->get()->map(function ($client) {
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
     * @param \App\Actions\CreateClient $action
     * @return \Illuminate\Contracts\Support\Responsable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateClientRequest $request, CreateClient $action)
    {
        $data = $request->filtered();
        $this->authorize('create', [Client::class, $data]);

        // Look for duplicates
        if (! $request->override) {
            if ($request->email && Client::forRequestedBusinesses()->whereEmail($request->email)->first()) {
                return new ConfirmationResponse('There is already a client with the email address ' . $request->email . '.');
            }
            if (Client::forRequestedBusinesses()->whereName($request->firstname, $request->lastname)->first()) {
                return new ConfirmationResponse('There is already a client with the name ' . $request->firstname . ' ' . $request->lastname . '.');
            }
        }
        $data['created_by'] = auth()->id();

        $paymentMethod = $request->provider_pay ? $request->getBusiness() : null;

        if ($client = $action->create($data, $paymentMethod)) {
            return new CreatedResponse('The client has been created.', ['id' => $client->id, 'url' => route('business.clients.edit', [$client->id])]);
        }

        return new ErrorResponse(500, 'The client could not be created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client $client
     * @param \App\Billing\Queries\OnlineClientInvoiceQuery $invoiceQuery
     * @return ErrorResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Client $client, OnlineClientInvoiceQuery $invoiceQuery)
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
            'payments.invoices',
            'user.documents',
            'medications',
            'meta',
            'notes.creator',
            'careDetails',
            'carePlans',
            'caseManager',
            'deactivationReason',
            'payers',
            'rates',
            'notes' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            },
            'contacts',
        ])
        ->append('last_service_date');
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
        if (! empty($client->default_payment_id)) {
            $client->defaultPayment->charge_metrics = $client->defaultPayment->charge_metrics;
        }
        if (! empty($client->backup_payment_id)) {
            $client->backupPayment->charge_metrics = $client->backupPayment->charge_metrics;
        }
        $client->future_schedules = $client->futureSchedules()->count();
        $client->setup_url = $client->setup_url;

        $lastStatusDate = $client->agreementStatusHistory()->orderBy('created_at', 'DESC')->value('created_at');
        $business = $this->business();
        $services = Service::forAuthorizedChain()->ordered()->get();
        $payers = Payer::forAuthorizedChain()->ordered()->get();
        $auths = (new ClientAuthController())->listByClient($client->id);
        $invoices = $invoiceQuery->forClient($client->id, false)->get();

        $salesPeople = SalesPerson::forRequestedBusinesses()
            ->whereActive()
            ->orWhere('id', $client->sales_person_id)
            ->get();

        return view('business.clients.show', compact('client', 'caregivers', 'lastStatusDate', 'business', 'salesPeople', 'payers', 'services', 'auths', 'invoices'));
    }

    public function edit(Client $client)
    {
        return $this->show($client, app(OnlineClientInvoiceQuery::class));
    }

    /**
     * Update the client profile.
     *
     * @param UpdateClientRequest $request
     * @param Client $client
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);
        $data = $request->filtered();

        $addOnboardRecord = false;
        if ($client->agreement_status != $data['agreement_status']) {
            $addOnboardRecord = true;
        }

        \DB::beginTransaction();
        if ($client->update($data)) {
            if ($addOnboardRecord) {
                $client->agreementStatusHistory()->create(['status' => $data['agreement_status']]);
            }

            \DB::commit();
            return new SuccessResponse('The client has been updated.', $client, '.');
        }

        \DB::rollBack();
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

        $data = request()->all();

        try {
            $data['inactive_at'] = request('inactive_at') ? Carbon::parse(request('inactive_at')) : Carbon::now();
        } catch (\Exception $ex) {
            return new ErrorResponse(422, 'Invalid inactive date.');
        }

        if (request()->filled('reactivation_date')) {
            $data['reactivation_date'] = Carbon::parse(request('reactivation_date'));
        }

        $data['status_alias_id'] = null;
        if ($client->update($data)) {
            $client->clearFutureSchedules();
            return new SuccessResponse('The client has been archived.', [], route('business.clients.index'));
        }
        return new ErrorResponse(500, 'Could not archive the selected client.');
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

        if ($client->update(['active' => true, 'inactive_at' => null, 'status_alias_id' => null])) {
            $client->clearFutureSchedules();
            return new SuccessResponse('The client has been re-activated.', null, '.');
        }
        return new ErrorResponse(500, 'Could not re-activate the selected client.');
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

    public function getPaymentType(Client $client)
    {
        return [
            'payment_type' => $client->getPaymentType(),
            'percentage_fee' => $client->getAllyPercentage(),
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
            'medicaid_diagnosis_codes',
            'max_weekly_hours'
        ]);

        if ($client->update($data)) {
            return new SuccessResponse('Client info updated.');
        } else {
            return new ErrorResponse(500, 'Error updating client info.');
        }
    }

    /**
     * Update the Client's preferences.
     *
     * @param UpdateClientPreferencesRequest $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function preferences(UpdateClientPreferencesRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->setPreferences(array_except($request->validated(), 'ethnicities'));
        $client->preferences->ethnicities()->delete();
        $client->preferences->ethnicities()->saveMany(
            collect($request->ethnicities)->map(function ($ethnicity) {
                return new ClientEthnicityPreference(compact('ethnicity'));
            })
        );

        return new SuccessResponse('Client preferences updated.', $client->preferences->fresh());
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

    /**
     * Send welcome email to the client.
     *
     * @param Client $client
     * @return \Illuminate\Http\Response
     */
    public function welcomeEmail(Client $client)
    {
        $client->update(['welcome_email_sent_at' => Carbon::now()]);

        $client->notify(new ClientWelcomeEmail($client));

        // Use the reload page redirect to update the welcome_emaiL_sent_at timestamp
        return new SuccessResponse('A welcome email was dispatched to the Client.', null, '.');
    }

    /**
     * Send training email to the client.
     *
     * @param Client $client
     * @return \Illuminate\Http\Response
     */
    public function trainingEmail(Client $client)
    {
        $client->update(['training_email_sent_at' => Carbon::now()]);

        $client->notify(new TrainingEmail());

        // Use the reload page redirect to update the timestamp
        return new SuccessResponse('A training email was dispatched to the Client.', null, '.');
    }
}
