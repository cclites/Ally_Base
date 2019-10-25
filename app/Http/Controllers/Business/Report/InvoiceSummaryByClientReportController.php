<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Requests\InvoiceSummaryByClientReportRequest;
use App\Reports\InvoiceSummaryByClientReport;
use App\Http\Controllers\Controller;

class InvoiceSummaryByClientReportController extends Controller
{
    /**
     * Get the Invoice Summary by Client Report.
     *
     * @param InvoiceSummaryByClientReportRequest $request
     * @param InvoiceSummaryByClientReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(InvoiceSummaryByClientReportRequest $request, InvoiceSummaryByClientReport $report)
    {
        if ($request->wantsReportData()) {
            $report->query()->forRequestedBusinesses();

            $report->applyFilters(
                $request->mode,
                $request->filterDateRange(),
                $request->client_type,
                $request->payer_id,
                $request->client_id
            );

            if ($request->forExport()) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }

            return response()->json(['results' => $report->rows()]);
        }

        return view_component('invoice-summary-by-client-report', 'Invoice Summary by Client Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}