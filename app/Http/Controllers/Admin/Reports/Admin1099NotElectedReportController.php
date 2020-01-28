<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Business;
use App\Http\Controllers\Controller;
use App\Reports\Admin1099NotElectedReport;
use App\Reports\Caregiver1099PreviewReport;
use Illuminate\Http\Request;

class Admin1099NotElectedReportController extends Controller
{
    /**
     * Get the admin 1099 preview report.
     *
     * @param Request $request
     * @param Admin1099NotElectedReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, Admin1099NotElectedReport $report)
    {
        if (filled($request->json)) {
            $request->validate([
                'year' => 'required',
                'business_id' => 'nullable|numeric',
                'payer' => 'nullable|in:ally,client',
                'client_id' => 'nullable|numeric',
                'caregiver_id' => 'nullable|numeric',
            ]);

            if (empty($request->business_id)) {
                $businesses = Business::pluck('id');
            } else {
                $businesses = [$request->business_id];
            }

            $report->applyFilters(
                $request->year,
                $request->caregiver_id,
                $request->client_id,
                $businesses,
                $request->payer
            );

            return response()->json($report->rows());
        }

        return view_component(
            'admin-1099-not-elected-report',
            '1099 Caregivers Not Elected Report',
            [],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
            ]
        );
    }
}
