<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Caregiver1099;
use App\Admin\Queries\Caregiver1099Query;
use App\Reports\Admin1099PreviewReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Admin1099PreviewReportController extends Controller
{
    public function index(Admin1099PreviewReport $report, Request $request){

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
                                'client_id'=>$request->client_id,
                                'caregiver_id'=>$request->caregiver_id,
                                'caregiver_1099'=>$request->caregiver_1099,
                                'status'=>$request->status,
                                'transmission'=>$request->transmission,
                                'caregiver_1099_id'=>$request->caregiver_1099_id
                            ]);

            return response()->json($caregiver1099s);
        }

        return view_component(
            'admin-1099-preview',
            '1099 Preview Report',
            [],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
            ]
        );

    }

}
