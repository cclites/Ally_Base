<?php


namespace App\Http\Controllers\Business\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reports\CaregiverOvertimeReport;

class BusinessCaregiverOvertimeReportController extends Controller
{

    public function index(Request $request, CaregiverOvertimeReport $report){

        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start,
                        $request->end,
                        $request->caregiver_id,
                        $request->active
                    );

            return response()->json($report->rows());

        }

        return view('business.reports.overtime');


    }

}