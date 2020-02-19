<?php


namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Http\Controllers\Controller;
use App\Reports\AvailableShiftReport;
use App\Schedule;
use Illuminate\Http\Request;


class AvailableShiftsReportController extends Controller
{
    public function index(Request $request, AvailableShiftReport $report){

        if( filled($request->json) || filled($request->export) ){

            $report->applyFilters(
                $request->businesses,
                $request->start,
                $request->end,
                $request->client_id,
                $request->city,
                $request->service
            );

            if ( filled($request->export) ) {
                return $report;
            }

            return response()->json($report->rows());

        }

        return view_component(
            'business-available-shifts',
            'Available Shifts Report',
            [],
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );

    }


}