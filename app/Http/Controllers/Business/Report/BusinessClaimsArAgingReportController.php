<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use App\Reports\BusinessClaimsArAgingReport;
use Illuminate\Http\Request;

class BusinessClaimsArAgingReportController extends Controller
{
    /**
     * Get the Payroll Export Report
     *
     * @param Request $request
     * @param BusinessClaimsArAgingReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, BusinessClaimsArAgingReport $report)
    {
        if ($request->filled('json')) {
            $report->query()->forRequestedBusinesses();

            $report->forClient($request->client_id ?? null)
                ->forPayer($request->payer_id ?? null);

            return response()->json($report->rows());
        }

        return view_component('business-claims-ar-aging-report', 'Claims AR Aging Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}