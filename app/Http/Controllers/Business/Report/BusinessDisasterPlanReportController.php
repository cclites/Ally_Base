<?php

namespace App\Http\Controllers\Business\Report;

use App\Client;
use App\DisasterCode;
use Illuminate\Http\Request;
use App\Reports\DisasterPlanReport;
use App\Http\Controllers\Controller;

class BusinessDisasterPlanReportController extends Controller
{
    /**
     * Get the Disaster Code Plan Report.
     *
     * @param Request $request
     * @param DisasterPlanReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, DisasterPlanReport $report)
    {
        if ($request->filled('json')) {
            $report = $report->forBusiness($request->business_id)
                ->forClient($request->client_id)
                ->withStatus($request->client_status)
                ->forZipcode($request->zipcode);

            if ($request->filled('disaster_code')) {
                $code = '_' . $request->disaster_code;
                $report->forDisasterCode(DisasterCode::$code());
            }

            return response()->json($report->rows());
        }

        return view_component('business-disaster-plan-report', 'Disaster Code Plan Report', ['clients' => $this->getClientList()], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Get list of clients for the clients dropdown filter.
     *
     * @return array
     */
    protected function getClientList() : array
    {
        return Client::forRequestedBusinesses()
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->nameLastFirst,
                    'business_id' => $client->business_id,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }
}
