<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Caregiver1099;
use App\Admin\Queries\Caregiver1099Query;
use App\Reports\Admin1099PreviewReport;
use App\Reports\Caregiver1099Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Admin1099PreviewReportController extends Controller
{
    public function index(Request $request, Caregiver1099Report $report)
    {
        if (filled($request->json)) {
            \DB::enableQueryLog();

            $request->validate([
                'year' => 'required',
                'business_id' => 'required|numeric',
                'payer' => 'nullable|in:ally,client',
                'client_id' => 'nullable|numeric',
                'caregiver_id' => 'nullable|numeric',
                'created' => 'nullable|in:1,0',
            ]);

            $createdStatus = $request->created == '1' ? true : false;
            if (empty($request->created)) {
                $createdStatus = null;
            }

            $report->applyFilters(
                $request->year,
                $request->caregiver_id,
                $request->client_id,
                $request->business_id,
                $request->payer,
                $createdStatus
            );

            $results = $report->rows();

            \Log::info(\DB::getQueryLog());

            return response()->json($results);
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
