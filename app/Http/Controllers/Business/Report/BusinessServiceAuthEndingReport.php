<?php

namespace App\Http\Controllers\Business\Report;

use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientDropdownResource;
use App\Reports\ServiceAuthEndingReport;
use Illuminate\Http\Request;

class BusinessServiceAuthEndingReport extends Controller
{
    /**
     * Get the Service Authorizations Ending Report
     *
     * @param Request $request
     * @param ServiceAuthEndingReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, ServiceAuthEndingReport $report)
    {
        if ($request->filled('json')) {
            $clientQuery = Client::forRequestedBusinesses();
            if (filled($request->client_id)) {
                $clientQuery->where('id', $request->client_id);
            }

            $results = $report->applyFilters(
                $clientQuery->pluck('id'),
                $request->days,
                auth()->user()->role->getTimezone()
            )->rows();

            return response()->json($results);
        }

        return view_component('business-service-auth-ending-report', 'Service Authorizations Ending Report', [], [
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