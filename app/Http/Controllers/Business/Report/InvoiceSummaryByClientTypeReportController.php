<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Requests\InvoiceSummaryByClientTypeReportRequest;
use App\Reports\InvoiceSummaryByClientTypeReport;
use App\Http\Controllers\Controller;

class InvoiceSummaryByClientTypeReportController extends Controller
{
    /**
     * Get the Claim Remit Application Report.
     *
     * @param InvoiceSummaryByClientTypeReportRequest $request
     * @param InvoiceSummaryByClientTypeReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(InvoiceSummaryByClientTypeReportRequest $request, InvoiceSummaryByClientTypeReport $report)
    {
        if ($request->wantsReportData()) {
            $report->query()->forRequestedBusinesses();

            $report->applyFilters(
                $request->mode,
                $request->filterDateRange()
            );

            if ($request->forExport()) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }

            return response()->json(['results' => $report->rows()]);
        }

        return view_component('invoice-summary-by-client-type-report', 'Invoice Summary by Client Type Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}