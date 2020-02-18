<?php


namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use App\Schedule;


class AvailableShiftsReportController extends Controller
{
    public function index(\Request $request){

        if($request->json || $request->print){

            $schedules = Schedule::forRequestedBusinesses()
                        ->with(['client', 'services'])


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