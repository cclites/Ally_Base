<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Controller;
use App\Reports\BusinessClaimsArAgingReport;
use Illuminate\Http\Request;

class ClaimsArAgingReportController extends Controller
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
        if ($request->filled('json') || $request->input('export')) {
            $report->query()->forRequestedBusinesses();

            $report->forClient($request->client_id ?? null)
                ->forPayer($request->payer_id ?? null);

            if ($request->input('export')) {
                return $report->setDateFormat('m/d/Y g:i A', 'America/New_York')
                    ->download();
            }

            return response()->json($report->rows());
        }

        return view_component('business-claims-ar-aging-report', 'Claims AR Aging Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}