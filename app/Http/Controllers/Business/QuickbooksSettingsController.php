<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\Resources\QuickbooksConnectionResource;
use App\Responses\SuccessResponse;
use App\Rules\ValidActivityCode;
use App\Services\QuickbooksOnlineService;
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
                'clients' => $this->getClients(),
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

        $business->quickbooksConnection()->delete();

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
            $client = Client::where('business_id', $business->id)->where('id', $mapping['id'])->first();
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
     * Get a list of clients for use with customer mapping.
     *
     * @return array
     */
    protected function getClients()
    {
        return Client::forRequestedBusinesses()
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
        // TODO: do we need to remove customers that no longer appear via API ?
        foreach ($api->getCustomers() as $customer) {
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
        foreach ($api->getItems() as $service) {
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
     * @param Request $request
     * @param Business $business
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateSettings(Request $request, Business $business)
    {
        if (empty($business->quickbooksConnection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }

        $this->authorize('update', $business);

        $data = $request->validate([
            'name_format' => 'required|in:first_last,last_first',
            'mileage_service_id' => 'nullable|exists:quickbooks_services,id',
            'refund_service_id' => 'nullable|exists:quickbooks_services,id',
            'shift_service_id' => 'nullable|exists:quickbooks_services,id',
            'expense_service_id' => 'nullable|exists:quickbooks_services,id',
            'adjustment_service_id' => 'nullable|exists:quickbooks_services,id',
        ]);

        $business->quickbooksConnection->update($data);

        return new SuccessResponse('Settings updated successfully.');
    }
}
