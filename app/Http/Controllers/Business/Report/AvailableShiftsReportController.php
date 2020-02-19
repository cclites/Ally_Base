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

        //$this->authorize('read', Business::find($request->businesses));

        if($request->json || $request->print){

            $report->applyFilters(
                $request->businesses,
                $request->start,
                $request->end,
                $request->client,
                $request->city,
                $request->service
            );

            if ($request->print) {
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