<?php

namespace App\Http\Controllers\Business\Report;

use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientDropdownResource;
use App\Reports\ServiceAuthUsageReport;
use Illuminate\Http\Request;

class BusinessServiceAuthUsageReport extends Controller
{
    /**
     * Get the Service Authorizations Usage Report
     *
     * @param Request $request
     * @param ServiceAuthUsageReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, ServiceAuthUsageReport $report)
    {
        if ($request->filled('json')) {
            $client = Client::findOrFail($request->client_id);
            $this->authorize('read', $client);

            $results = $report->setClient($client)
                ->setDateRange($request->start_date, $request->end_date)
                ->rows();

            return response()->json($results);
        }

        return view_component('business-service-auth-usage-report', 'Service Authorizations Usage Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Get the clients for the Client Filter.
     *
     * @param Request $request
     * @return ClientDropdownResource
     */
    public function clients(Request $request)
    {
        $clients = Client::forRequestedBusinesses()
            ->active()
            ->ordered()
            ->get();

        return new ClientDropdownResource($clients);
    }
}