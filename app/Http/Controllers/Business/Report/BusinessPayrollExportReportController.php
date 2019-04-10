<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use App\Reports\PayrollExportReport;
use Illuminate\Http\Request;

class BusinessPayrollExportReportController extends Controller
{
    /**
     * Get the Payroll Export Report
     *
     * @param Request $request
     * @param PayrollExportReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, PayrollExportReport $report)
    {
        if ($request->filled('json') || $request->filled('export')) {
            $report = $report->where('business_id', $request->business_id)
                ->forDates($request->start, $request->end, auth()->user()->role->getTimezone())
                ->inFormat($request->input('output_format', PayrollExportReport::ADP));

            if ($request->export == 1) {
                return $report->downloadCsv();
            }

            return response()->json($report->rows());
        }

        return view_component('business-payroll-export-report', 'Payroll Export Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}