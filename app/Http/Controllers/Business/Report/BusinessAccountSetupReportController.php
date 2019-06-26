<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use App\Reports\BusinessClaimsArAgingReport;
use App\Reports\CaregiverAccountSetupReport;
use Illuminate\Http\Request;

class BusinessAccountSetupReportController extends Controller
{
    /**
     * Get the Payroll Export Report
     *
     * @param Request $request
     * @param CaregiverAccountSetupReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, CaregiverAccountSetupReport $report)
    {
        if ($request->filled('json')) {
            $report->query()->forRequestedBusinesses();

            switch ($request->status) {
                case 'scheduled':
                    $report->query()->active()->whereScheduled()->whereNotSetup();
                    break;
                default:
                    $report->query()->active()->whereNotSetup();
                    break;
            }

            $rows = $report->rows();

            switch ($request->phone) {
                case 'has_mobile':
                    $rows = $rows->filter(function ($row) {
                        return filled($row['mobile_phone']);
                    });
                    break;
                case 'any':
                    $rows = $rows->filter(function ($row) {
                        return filled($row['mobile_phone']) || filled($row['home_phone']);
                    });
                    break;
                case 'none':
                    $rows = $rows->filter(function ($row) {
                        return empty($row['mobile_phone']) && empty($row['home_phone']);
                    });
                    break;
            }

            if ($request->input('export')) {
                return $report->setDateFormat('m/d/Y g:i A', 'America/New_York')
                    ->download();
            }

            return response()->json($rows->values());
        }

        return view_component('business-account-setup-report', 'Caregiver Account Setup Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}