<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Caregiver1099;
use App\Admin\Queries\Caregiver1099Query;
use App\Reports\Ally1099PayerReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Ally1099PayerReportController extends Controller
{
    public function index(Ally1099PayerReport $report, Request $request){

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
                                'caregiver_id'=>$request->caregiver_id,
                            ]);

            return response()->json($caregiver1099s);
        }

        return view_component(
            'ally-1099-preview',
            '1099 Preview Report',
            [],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
            ]
        );

    }

}
