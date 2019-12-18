<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Reports\Caregiver1099PreviewReport;
use Illuminate\Http\Request;

class Admin1099PreviewReportController extends Controller
{
    /**
     * Get the admin 1099 preview report.
     *
     * @param Request $request
     * @param Caregiver1099PreviewReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, Caregiver1099PreviewReport $report)
    {
        if (filled($request->json)) {
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
