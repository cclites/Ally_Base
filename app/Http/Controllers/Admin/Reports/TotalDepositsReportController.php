<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Reports\TotalDepositsReport;
use Illuminate\Http\Request;

class TotalDepositsReportController
{
    public function index(Request $request, TotalDepositsReport $report)
    {
        if ($request->filled('json')) {
            $report->setTimezone(auth()->user()->getTimezone())
                ->applyFilters(
                    $request->start_date,
                    $request->end_date);

            $data = $report->rows();
            $totals = [
                'amount' => $report->getTotalAmount()
            ];

            return response()->json(['data' => $data, 'totals' => $totals]);
        }

        return view_component(
            'total-deposits-report',
            'Total Deposits Report',
            [],
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );
    }
}