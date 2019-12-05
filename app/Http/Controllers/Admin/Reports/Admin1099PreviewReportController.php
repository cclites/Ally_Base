<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Caregiver1099;
use App\Admin\Queries\Caregiver1099Query;
use App\Reports\Admin1099PreviewReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Admin1099PreviewReportController extends Controller
{
    public function index(Request $request){

        if($request->json){

            $request->validate([
                'year'=> 'required',
                'business_id' => 'required',
            ]);

            $data = new Admin1099PreviewReport(
                $request->year,
                $request->business_id,
                $request->client_id,
                $request->caregiver_id,
                $request->caregiver_1099,
                $request->status,
                $request->transmission,
                $request->caregiver_1099_id
            );

            $reports = $data->report();

            $records = collect($reports)->map(function($report){

                return[
                    'client_fname' => $report->client_fname,
                    'client_lname' => $report->client_lname,
                    'caregiver_fname' => $report->caregiver_fname,
                    'caregiver_lname' => $report->caregiver_lname,
                    'business_name' => $report->business_name,
                    'payment_total' => $report->caregiver_1099_amount ? $report->caregiver_1099_amount : $report->payment_total,
                    'caregiver_1099_amount' => $report->caregiver_1099_amount,
                    'caregiver_1099' => $report->caregiver_1099,
                    'caregiver_1099_id' => $report->caregiver_1099_id,
                    'caregiver_id' => $report->caregiver_id,
                    'client_id' => $report->client_id,
                    'transmitted' => $report->transmitted_at,
                    'id' => $report->caregiver_1099_id,
                ];

            });

            return response()->json($records);
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
