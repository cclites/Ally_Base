<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Reports\Caregiver1099PreviewReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminBad1099ReportController extends Controller
{
    /**
     * Get Bad 1099 Report.
     *
     * @param Request $request
     * @param Caregiver1099PreviewReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, Caregiver1099PreviewReport $report)
    {
        if ($request->json == 1) {
            $request->validate([
                'year' => 'required|numeric',
                'business_id' => 'required|numeric',
            ]);

            $report->applyFilters($request->year, null, null, $request->business_id);

            $results = $report->rows()->map(function ($item) {
                return [
                    'caregiver' => $item['caregiver_last_name'] . ", " . $item['caregiver_first_name'],
                    'client' => $item['client_last_name'] . ", " . $item['client_first_name'],
                    'caregiver_id' => $item['caregiver_id'],
                    'client_id' => $item['client_id'],
                    'location' => $item['business_name'],
                    'errors' => $item['errors'] ? implode(", ", $item['errors']) : false,
                ];
            })
            ->filter(function ($item) {
                return $item['errors'] !== false;
            });

            return response()->json($results->values());
        }

        return view_component(
            'bad-1099-report',
            'Bad 1099 Report',
            [],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
            ]
        );
    }
}