<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Client;
use App\Responses\ErrorResponse;
use App\Rules\ValidActivityCode;
use App\Services\QuickbooksOnlineService;
use Illuminate\Http\Request;

class BusinessQuickbooksSettingsController extends BaseController
{
    /**
     * Get the main quickbooks settings page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $clients = $this->getClients();
        $caregivers = $this->getCaregivers();
        $breadcrumbs = [
            'Home' => route('home'),
            'Settings' => null,
        ];

        return view_component(
            'business-quickbooks-settings',
            'Quickbooks Settings',
            compact('clients', 'caregivers'),
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

            return redirect(app(QuickbooksOnlineService::class)->getAuthorizationCodeURL());

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

            // TODO: how the fuck do you find the current business
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
//            \Log::info($ex->getMessage());
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
