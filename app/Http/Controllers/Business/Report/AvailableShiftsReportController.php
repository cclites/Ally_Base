<?php


namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Http\Controllers\Controller;
use App\Reports\AvailableShiftReport;
use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


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
                $rows = $report->rows()->values()->toArray();

                $client = [];
                $start = (new Carbon($request->start . ' 00:00:00'))->format('m/d/Y');
                $end = (new Carbon($request->end . ' 23:59:59'))->format('m/d/Y');
                $city = $request->city;
                $business = Business::find($request->businesses);

                if($request->client_id && $request->client_id > 0){
                    $client = \App\Client::find($request->client_id);
                }

                $pdf = PDF::loadView('business.reports.print.available_shift_report', compact('rows', 'business', 'client', 'start', 'end', 'city'));
                return $pdf->download(strtolower(Str::slug( 'Available Shifts')) . '.pdf');
            }

            $data = $report->rows()->values()->toArray();

            return response()->json($data);

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

    public function export($report){

    }


}