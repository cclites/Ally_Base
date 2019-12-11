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

            //NOTE: These are not actual caregiver1099s, they are
            $caregiver1099s = $data->report();

            $records = collect($caregiver1099s)->map(function($caregiver1099){

                return[
                    'client_fname' => $caregiver1099->client_fname,
                    'client_lname' => $caregiver1099->client_lname,
                    'caregiver_fname' => $caregiver1099->caregiver_fname,
                    'caregiver_lname' => $caregiver1099->caregiver_lname,
                    'business_name' => $caregiver1099->business_name,
                    'payment_total' => $caregiver1099->caregiver_1099_amount ? $caregiver1099->caregiver_1099_amount : $caregiver1099->payment_total,
                    'caregiver_1099_amount' => $caregiver1099->caregiver_1099_amount,
                    'caregiver_1099' => $caregiver1099->caregiver_1099,
                    'caregiver_1099_id' => $caregiver1099->caregiver_1099_id,
                    'caregiver_id' => $caregiver1099->caregiver_id,
                    'client_id' => $caregiver1099->client_id,
                    'transmitted' => $caregiver1099->transmitted_at,
                    'errors' => Caregiver1099::getErrors($caregiver1099),
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
