<?php

namespace App\Http\Controllers\Admin\Reports;

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

            $data = $report->applyFilters(
                $request->year,
                $request->business_id,
                $request->client_id,
                $request->caregiver_id,
                $request->caregiver_1099,
                $request->created,
                $request->transmitted
            );

            return response()->json($data);
        }

        return view_component(
            'admin-1099-preview',
            '1099 Preview Report',
            [
                'Home' => route('home'),
                'Reports' => route('admin.reports.index')
            ]
        );

    }
}
