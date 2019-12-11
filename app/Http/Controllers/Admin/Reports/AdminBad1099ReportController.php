<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Reports\Bad1099Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminBad1099ReportController extends Controller
{
    public function index(Bad1099Report $report, Request $request){

        if($request->json){

            $request->validate([
                'year'=> 'required',
                'business_id' => 'required',
            ]);

            //NOTE: These are not actual caregiver1099s, they are representations of what should
            //      be in a 1099.
            $caregiver1099s = $report->applyFilters([
                'year'=>$request->year,
                'business_id'=>$request->business_id,
            ]);

            return response()->json($caregiver1099s);
        }

        return view_component(
            'bad-1099-report',
            'Bad 1099 Report',
            [
                'Home' => route('home'),
                'Reports' => route('admin.admin-1099-actions')
            ]
        );
    }
}
