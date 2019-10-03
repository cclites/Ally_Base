<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Requests\ClaimTransmissionsReportRequest;
use App\Claims\Reports\ClaimTransmissionsReport;
use App\Http\Controllers\Controller;

class ClaimTransmissionsReportController extends Controller
{
    /**
     * Get the Claims Invoice AR Aging Report.
     *
     * @param ClaimTransmissionsReportRequest $request
     * @param ClaimTransmissionsReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(ClaimTransmissionsReportRequest $request, ClaimTransmissionsReport $report)
    {
        if ($request->forJson() || $request->forExport()) {
            $report->query()->forRequestedBusinesses();

            $report->forClient($request->client_id ?? null)
                ->forDateRange($request->filterDateRange())
                ->forClientType($request->client_type ?? null)
                ->showInactive($request->inactive == 1);

            if ($request->forExport()) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }

            return response()->json(array_merge(['results' => $report->rows()], $report->getTotals()));
        }

        return view_component('business-claim-transmissions-report', 'Claim Transmissions Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}