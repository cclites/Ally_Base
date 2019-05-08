<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\Caregiver;
use App\Client;
use App\QuickbooksCustomer;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidActivityCode;
use App\Services\QuickbooksOnlineService;
use Illuminate\Http\Request;

class BusinessQuickbooksSettingsController extends BaseController
{
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

    public function customersList(Business $business)
    {
        $this->authorize('read', $business);

        return response()->json($business->quickbooksCustomers()->ordered()->get());
    }

    public function customersSync(Business $business)
    {
        $this->authorize('read', $business);

        if (empty($business->quickbooksConnection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }

        $api = $business->quickbooksConnection->getApiService();
        if (empty($api)) {
            return new ErrorResponse(401, 'Error connecting to the Quickbooks API.  Please try again.');
        }

        // TODO: do we need to remove customers that no longer appear via API ?
        $customers = $api->getCustomers();
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

        return new SuccessResponse('Customer data successfully updated.', $business->quickbooksCustomers()->ordered()->get());
    }

    public function test()
    {
        // TODO: re-work this to include new default business & a office location dropdown
        $business = $this->business();

        if (empty($business->quickbooksConnection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }

        if ($api = $business->quickbooksConnection->getApiService()) {

            $customers = $api->getCustomers();

            dd($customers);

        } else {
            return new ErrorResponse(401, 'Error connecting to the Quickbooks API.  Please try again.');
        }
    }

    /**
     * Get the main quickbooks settings page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index()
    {
        // TODO: re-work this to include new default business & a office location dropdown
        $business = $this->business();
        $connection = optional($business->quickbooksConnection)->access_token;
        $authenticated = filled($connection) ? true : false;

        $clients = $this->getClients();
        $caregivers = $this->getCaregivers();
        $breadcrumbs = [
            'Home' => route('home'),
            'Settings' => null,
        ];

        return view_component(
            'business-quickbooks-settings',
            'Quickbooks Settings',
            compact('clients', 'caregivers', 'authenticated'),
            $breadcrumbs
        );
    }

    /**
     * Initiate Quickbooks API Connection.
     *
     * @return ErrorResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function connect()
    {
        try {

            return redirect(app(QuickbooksOnlineService::class)->getAuthorizationUrl());

        } catch (\Exception $ex) {

            app('sentry')->captureException($ex);
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
        try {
            $accessTokenObj = app(QuickbooksOnlineService::class)->getAccessToken($request->code, $request->realmId);

            if (empty($accessTokenObj)) {
                return new ErrorResponse(500, 'An error occurred while trying to connect to your Quickbooks account.  Please try again.');
            }

            // TODO: re-work this to include new default business & a office location dropdown
            $business = $this->business();
            if ($connection = $business->quickbooksConnection) {
                $connection->update([
                    'access_token' => $accessTokenObj,
                ]);
            } else {
                $business->quickbooksConnection()->create([
                    'access_token' => $accessTokenObj,
                ]);
            }

            return redirect(route('business.quickbooks.index'));

        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An error occurred while trying to connect to your Quickbooks account.  Please try again.');
        }
    }

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

    protected function getCaregivers()
    {
        return Caregiver::forRequestedBusinesses()
            ->active()
            ->get()
            ->map(function ($row) {
                return [
                'id' => $row->id,
                'name' => $row->name,
                'nameLastFirst' => $row->nameLastFirst,
                ];
            })
            ->sortBy('nameLastFirst')
            ->values()
            ->toArray();
    }
}
