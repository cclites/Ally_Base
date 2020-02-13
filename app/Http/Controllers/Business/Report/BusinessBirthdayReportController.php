<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use App\Reports\BirthdayReport;
use Illuminate\Http\Request;

class BusinessBirthdayReportController extends Controller
{
    /**
     * Get the user birthday report.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->filled('json')) {
            $report = new BirthdayReport($request->type);

            $userId = $request->selectedId;
            $clientType = $request->client_type;

            if (! $request->show_inactive) {
                $report->filterActiveOnly();
            }

            if ($userId != 'All' && $userId) {
                $report->filterByClientId($userId);
            }

            if ($clientType != 'All' && $clientType) {
                $report->filterByClientType($clientType);
            }

            if ($request->filterDates) {
                $report->filterByDateRange($request->days);
            }

            return response()->json($report->rows());
        }

        $type = $request->type == 'clients' ? 'clients' : 'caregivers';
        $type = ucfirst($type);

        return view('business.reports.user_birthday', compact('type'));
    }
}
