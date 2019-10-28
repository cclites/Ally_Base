<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentsVsDepositsReportRequest;
use App\Reports\PaymentsVsDepositsReport;

class PaymentsVsDepositsReportController extends Controller
{
    public function index(PaymentsVsDepositsReportRequest $request, PaymentsVsDepositsReport $report)
    {
        if ($request->forJson() || $request->forExport()) {

            $report->applyFilters($request->filterDateRange());

            if ($request->forExport()) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }

            return response()->json($report->rows());
        }

        return view_component('payments-vs-deposits-report', 'Payments vs Deposits Report', [], [
            'Home' => route('home'),
            'Reports' => route('admin.reports.payments-vs-deposits')
        ]);
    }
}