<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChargesVsDepositsReportRequest;
use App\Reports\ChargesVsDepositsReport;

class ChargesVsDepositsReportController extends Controller
{
    public function index(ChargesVsDepositsReportRequest $request, ChargesVsDepositsReport $report)
    {
        if ($request->forJson() || $request->forExport()) {

            $report->applyFilters($request->filterDateRange());

            if ($request->forExport()) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }

            return response()->json($report->rows());
        }

        return view_component('charges-vs-deposits-report', 'Charges vs Deposits Report', [], [
            'Home' => route('home'),
            'Reports' => route('admin.reports.charges-vs-deposits')
        ]);
    }
}