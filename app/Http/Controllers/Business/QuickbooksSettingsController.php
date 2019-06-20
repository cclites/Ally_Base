<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Http\Requests\UpdateQuickbooksSettingsRequest;
use App\Responses\ErrorResponse;
use App\Responses\Resources\QuickbooksConnectionResource;
use App\Responses\SuccessResponse;
use App\Rules\ValidActivityCode;
use App\Services\QuickbooksOnlineService;
use App\Shift;
use Illuminate\Http\Request;
use Session;

class QuickbooksSettingsController extends BaseController
{
    /**
     * Get the main quickbooks settings page.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() && $request->filled('json')) {
            $business = Business::findOrFail($request->business_id);
            $this->authorize('read', $business);

            if (empty($business->quickbooksConnection)) {
                return ['clients' => [], 'connection' => []];
            }

            return response()->json([
                'clients' => $this->getClients($business),
                'connection' => new QuickbooksConnectionResource($business->quickbooksConnection),
            ]);
        }

        return view_component(
            'business-quickbooks-settings',
            'Quickbooks Settings',
            [],
            [
                'Home' => route('home'),
                'Settings' => null,
            ]
        );
    }

    /**
     * Initiate Quickbooks API Connection.
     *
     * @param \App\Business $business
     * @return ErrorResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function connect(Business $business)
    {
        try {

            $this->authorize('update', $business);
            Session::put('quickbooks_business_id', $business->id);

            return redirect(app(QuickbooksOnlineService::class)
                ->getAuthorizationUrl());

        } catch (\Exception $ex) {

            app('sentry')->captureException($ex);
            \Log::info($ex);
            return new ErrorResponse(500, 'Quickbooks API not configured.');

        }
    }

    /**
     * Create and save the access token from the
     * authorization redirect.
     *
     * @param Request $request
     * @return ErrorResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function authorization(Request $request)
    {
        $businessId = Session::get('quickbooks_business_id');
        $business = Business::findOrFail($businessId);

        try {
            $accessTokenObj = app(QuickbooksOnlineService::class)->getAccessToken($request->code, $request->realmId);
            if (empty($accessTokenObj)) {
                return new ErrorResponse(500, 'An error occurred while trying to connect to your Quickbooks account.  Please try again.');
            }

            $this->authorize('update', $business);

            if ($connection = $business->quickbooksConnection) {
                $connection->update([
                    'access_token' => $accessTokenObj,
                ]);
            } else {
                $business->quickbooksConnection()->create([
                    'access_token' => $accessTokenObj,
                ]);
            }

            $connection = $business->fresh()->quickbooksConnection;
            $api = $connection->getApiService();
            $connection->update(['company_name' => $api->getCompanyName()]);

            $this->syncCustomerData($api, $business);
            $this->syncServiceData($api, $business);

            return redirect(route('business.quickbooks.index'));

        } catch (\Exception $ex) {
            \Log::info($ex);
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An error occurred while trying to connect to your Quickbooks account.  Please try again.');
        }
    }

    /**
     * Remove the businesses Quickbooks connections.
     *
     * @param Business $business
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function disconnect(Business $business)
    {
        $this->authorize('update', $business);

        if ($connection = $business->quickbooksConnection) {
            $connection->update(['access_token' => null]);
        }

        return new SuccessResponse('Your account has been disconnected from the Quickbooks API.', [], '.');
    }

    /**
     * Get the list of quickbooks customers.
     *
     * @param Business $business
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function customersList(Business $business)
    {
        $this->authorize('read', $business);

        return response()->json($business->quickbooksCustomers()->ordered()->get());
    }

    /**
     * Update the customer mapping settings.
     *
     * @param Request $request
     * @param Business $business
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function customersUpdate(Request $request, Business $business)
    {
        $this->authorize('read', $business);

        \DB::beginTransaction();

        foreach ($request->clients as $mapping) {
            $client = Client::where('business_id', $business->id)
                ->where('id', $mapping['id'])
                ->first();

            if (empty($client)) {
                return new ErrorResponse(500, 'Client #'.$mapping['id'].' not found, please refresh and try again.');
            }

            $client->update([
                'quickbooks_customer_id' => $mapping['quickbooks_customer_id'],
            ]);
        }

        \DB::commit();

        return new SuccessResponse('Client mappings have been saved.');
    }

    /**
     * Force refresh of quickbooks customer data.
     *
     * @param Business $business
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function customersSync(Business $business)
    {
        $this->authorize('update', $business);

        if (empty($business->quickbooksConnection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }

        $api = $business->quickbooksConnection->getApiService();
        if (empty($api)) {
            return new ErrorResponse(401, 'Error connecting to the Quickbooks API.  Please try again.');
        }

        $this->syncCustomerData($api, $business);

        return new SuccessResponse('Customer data successfully updated.', $business->quickbooksCustomers()->ordered()->get());
    }

    /**
     * Create a Quickbooks customer relationship
     * using the client data and update the mapping record.
     *
     * @param Request $request
     * @param Business $business
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \QuickBooksOnline\API\Exception\IdsException
     */
    public function customerCreate(Request $request, Business $business)
    {
        $client = Client::findOrFail($request->client_id);

        $this->authorize('update', $business);

        if (empty($business->quickbooksConnection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }

        /** @var QuickbooksOnlineService $api */
        $api = $business->quickbooksConnection->getApiService();
        if (empty($api)) {
            return new ErrorResponse(401, 'Error connecting to the Quickbooks API.  Please try again.');
        }

        // Create new customer relationship.
        [$customerId, $customerName] = $api->createCustomer($client);
        $customer = $client->quickbooksCustomer()->create([
            'business_id' => $business->id,
            'name' => $customerName,
            'customer_id' => $customerId,
        ]);
        $client->update(['quickbooks_customer_id' => $customer->id]);

        return new SuccessResponse('Customer record successfully created.', [
            'client' => $client,
            'customers' => $business->quickbooksCustomers()->ordered()->get()
        ]);
    }

    /**
     * Get a list of clients for use with customer mapping.
     *
     * @param \App\Business $business
     * @return array
     */
    protected function getClients(Business $business) : array
    {
        return $business->clients()
            ->active()
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'nameLastFirst' => $row->nameLastFirst,
                    'quickbooks_customer_id' => optional($row->quickbooksCustomer)->id ?? '',
                ];
            })
            ->sortBy('nameLastFirst')
            ->values()
            ->toArray();
    }

    /**
     * Download the customer data from the API and persist
     * it into the database.
     *
     * @param QuickbooksOnlineService $api
     * @param Business $business
     * @throws \Exception
     */
    protected function syncCustomerData(QuickbooksOnlineService $api, Business $business) : void
    {
        $customers = collect($api->getCustomers());

        // Find customer records that no longer appear in Quickbooks.
        $customerIds = $customers->pluck('Id');
        $deleteIds = $business->quickbooksCustomers()->whereNotIn('customer_id', $customerIds)->get()->pluck('id');

        // Remove any client->customer mappings to those missing customer records.
        Client::whereIn('quickbooks_customer_id', $deleteIds)
            ->update([
                'quickbooks_customer_id' => null,
            ]);

        // Delete the missing customer records.
        $business->quickbooksCustomers()->whereIn('id', $deleteIds)->delete();

        // Create OR update each customer record.
        foreach ($customers as $customer) {
            if ($match = $business->quickbooksCustomers()->where('customer_id', $customer->Id)->first()) {
                $match->update([
                    'name' => $customer->FullyQualifiedName,
                ]);
            } else {
                $business->quickbooksCustomers()->create([
                    'customer_id' => $customer->Id,
                    'name' => $customer->FullyQualifiedName,
                ]);
            }
        }
    }

    /**
     * Force refresh of quickbooks services data.
     *
     * @param Business $business
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function servicesSync(Business $business)
    {
        $this->authorize('update', $business);

        if (empty($business->quickbooksConnection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }

        $api = $business->quickbooksConnection->getApiService();
        if (empty($api)) {
            return new ErrorResponse(401, 'Error connecting to the Quickbooks API.  Please try again.');
        }

        $this->syncServiceData($api, $business);

        return new SuccessResponse('Services data successfully updated.', $business->quickbooksServices()->ordered()->get());
    }

    /**
     * Get the list of quickbooks services.
     *
     * @param Business $business
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function servicesList(Business $business)
    {
        $this->authorize('read', $business);

        return response()->json($business->quickbooksServices()->ordered()->get());
    }

    /**
     * Download the services data from the API and persist
     * it into the database.
     *
     * @param QuickbooksOnlineService $api
     * @param Business $business
     * @throws \Exception
     */
    protected function syncServiceData(QuickbooksOnlineService $api, Business $business) : void
    {
        $services = collect($api->getItems());

        // Find customer records that no longer appear in Quickbooks.
        $serviceIds = $services->pluck('Id');
        $deleteIds = $business->quickbooksServices()->whereNotIn('service_id', $serviceIds)->get()->pluck('id');

        // Remove any service mappings to those missing service records.
        $columns = ['shift_service_id', 'adjustment_service_id', 'refund_service_id', 'mileage_service_id', 'expense_service_id'];
        foreach ($columns as $column) {
            $business->quickbooksConnection()->whereIn($column, $deleteIds)->update([$column => null]);
        }

        // Keep reference to deleted services for now to allow for historical
        // accuracy.  Quickbooks doesn't allow actual deletion of items, so
        // the mapping still works.
//        $business->shifts()->whereIn('quickbooks_service_id', $deleteIds)->update(['quickbooks_service_id' => null]);
//        $business->schedules()->whereIn('quickbooks_service_id', $deleteIds)->update(['quickbooks_service_id' => null]);

        // Delete the missing service records.
        $business->quickbooksServices()->whereIn('id', $deleteIds)->delete();

        foreach ($services as $service) {
            if ($match = $business->quickbooksServices()->where('service_id', $service->Id)->first()) {
                $match->update([
                    'name' => $service->Name,
                ]);
            } else {
                $business->quickbooksServices()->create([
                    'service_id' => $service->Id,
                    'name' => $service->Name,
                ]);
            }
        }
    }

    /**
     * Update the general settings tab.
     *
     * @param UpdateQuickbooksSettingsRequest $request
     * @param Business $business
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateSettings(UpdateQuickbooksSettingsRequest $request, Business $business)
    {
        if (empty($business->quickbooksConnection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }
        $this->authorize('update', $business);

        $business->quickbooksConnection->update($request->validated());

        return new SuccessResponse('Settings updated successfully.');
    }

    /**
     * Get the quickbooks configuration for a given business.
     *
     * @param Business $business
     * @return QuickbooksConnectionResource|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function config(Business $business)
    {
        $this->authorize('read', $business);

        if (empty($business->quickbooksConnection)) {
            return response()->json([]);
        }

        return new QuickbooksConnectionResource($business->quickbooksConnection);
    }
}
