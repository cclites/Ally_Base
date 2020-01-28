<?php

namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\Client;
use App\Reports\ProjectedBillingReport;
use App\Http\Controllers\Controller;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectedBillingReportController extends Controller
{
    /**
     * Show/print the report.
     *
     * @param Request $request
     * @param ProjectedBillingReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, ProjectedBillingReport $report)
    {
        if ($request->filled('json')) {
            $data = $report->applyFilters(
                $request->start_date,
                $request->end_date,
                $request->client,
                $request->client_type,
                $request->caregiver,
                auth()->user()->role->getTimezone()
            )->rows();

            if ($request->filled('print')) {
                $pdf = PDF::loadView('business.reports.print.projected_billing', $data);
                return $pdf->download('projected_billing.pdf');
            }

            return response()->json($data);
        }

        return view_component('projected-billing-report', 'Projected Billing Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Get the filter options for the report.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterOptions()
    {
        $clients = Client::with('user')
            ->forRequestedBusinesses()
            ->active()
            ->select('id', 'client_type')
            ->get()
            ->map(function (Client $client) {
                return [
                    'id' => $client->id,
                    'name' => $client->nameLastFirst,
                    'client_type' => $client->client_type,
                ];
            })
            ->sortBy('name')
            ->values();

        $caregivers = Caregiver::with('user')
            ->forRequestedBusinesses()
            ->active()
            ->select('id')
            ->get()
            ->map(function (Caregiver $caregiver) {
                return [
                    'id' => $caregiver->id,
                    'name' => $caregiver->nameLastFirst,
                ];
            })
            ->sortBy('name')
            ->values();

        $clientTypes = $clients->pluck('client_type')
            ->unique()
            ->map(function ($item) {
                return [
                    'id' => $item,
                    'name' => Str::title(str_replace('_', ' ', $item)),
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json(compact('clients', 'clientTypes', 'caregivers'));
    }
}
