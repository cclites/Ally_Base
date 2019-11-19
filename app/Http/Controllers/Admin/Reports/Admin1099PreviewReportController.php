<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Caregiver1099;
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

            $caregiver1099s = new Caregiver1099(
                $request->all()
            );

            $records = $caregiver1099s->records();

            $data = collect($records)->map(function($record){

                return[
                    'client_fname' => $record->client_fname,
                    'client_lname' => $record->client_lname,
                    'caregiver_fname' => $record->caregiver_fname,
                    'caregiver_lname' => $record->caregiver_lname,
                    'business_name' => $record->business_name,
                    'payment_total' => $record->payment_total,
                    'caregiver_1099' => $record->caregiver_1099,
                    'caregiver_1099_id' => $record->caregiver_1099_id,
                    'caregiver_id' => $record->caregiver_id,
                    'client_id' => $record->client_id,
                    'transmitted' => $record->transmitted_at,
                ];

            });

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
