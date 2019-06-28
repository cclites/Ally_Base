<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use App\Reports\BusinessOfflineArAgingReport;
use Illuminate\Http\Request;

class BusinessOfflineArAgingReportController extends Controller
{
    /**
     * Get the Payroll Export Report
     *
     * @param Request $request
     * @param BusinessOfflineArAgingReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, BusinessOfflineArAgingReport $report)
    {
        if ($request->filled('json')) {
            $report->query()->forRequestedBusinesses();

            $report->forClient($request->client_id ?? null)
                ->forPayer($request->payer_id ?? null);

            return response()->json($report->rows());
        }

        return view_component('business-offline-ar-aging-report', 'Offline AR Aging Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}