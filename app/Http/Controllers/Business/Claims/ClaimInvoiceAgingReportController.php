<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Controller;
use App\Claims\Reports\ClaimInvoiceAgingReport;
use Illuminate\Http\Request;

class ClaimInvoiceAgingReportController extends Controller
{
    /**
     * Get the Claims Invoice AR Aging Report.
     *
     * @param Request $request
     * @param ClaimInvoiceAgingReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, ClaimInvoiceAgingReport $report)
    {
        if ($request->filled('json') || $request->input('export')) {
            $report->query()->forRequestedBusinesses();

            $report->forClient($request->client_id ?? null)
                ->forPayer($request->payer_id ?? null);

            if ($request->filled('export')) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }

            return response()->json([
                'results' => $report->rows(),
                'totals' => $report->totals(),
            ]);
        }

        return view_component('business-claim-invoice-aging-report', 'Claims Invoice Aging Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}