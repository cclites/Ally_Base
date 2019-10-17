<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Reports\ClaimRemitApplicationReport;
use App\Claims\Requests\ClaimRemitApplicationReportRequest;
use App\Http\Controllers\Controller;

class ClaimRemitApplicationReportController extends Controller
{
    /**
     * Get the Claim Remit Application Report.
     *
     * @param ClaimRemitApplicationReportRequest $request
     * @param ClaimRemitApplicationReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(ClaimRemitApplicationReportRequest $request, ClaimRemitApplicationReport $report)
    {
        if ($request->wantsReportData()) {
            $report->query()->forRequestedBusinesses();

            $report->applyFilters(
                $request->payer_id ?? null,
                $request->filterDateRange(),
                $request->type ?? null
            );

            if ($request->forExport()) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }

            return response()->json(['results' => $report->rows()]);
        }

        return view_component('business-remit-application-report', 'Remit Application Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}