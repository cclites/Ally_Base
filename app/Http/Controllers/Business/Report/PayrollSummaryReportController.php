<?php

namespace App\Http\Controllers\Business\Report;

//TODO: Add imports
use App\Business;
use App\Client;
use App\Reports\PayrollSummaryReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Business\BaseController;
use App\Http\Resources\CaregiverDropdownResource;
use App\Caregiver;

//TODO: Remove logging import before committing
use Log;

/**
 *
 * @package App\Http\Controllers\Business\Report
 */
//TODO: Change template class name
class PayrollSummaryReportController extends BaseController
{
    /**
     * @param Request
     * @param PayrollSummaryReport
     */
    //TODO: Inject proper report template
    public function index(Request $request, PayrollSummaryReport $report ){

        if ($request->filled('json')) {

           $timezone = auth()->user()->role->getTimezone();

            $data = $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start,
                        $request->end,
                        $request->business,
                        $request->client_type,
                        $request->caregiver
                    )->rows();

            $clientType = $request->client_type ? ucfirst(str_replace("_", " ", $request->client_type)) : "All Clients";
            $businessName = Business::find($request->business)->name;
            $caregiverName = $request->caregiver ? Caregiver::find($request->caregiver)->nameLastFirst() : 'All Caregivers';

            $totals = [
                'amount'=>$data->sum('amount'),
                'location' => $businessName,
                'caregiver' => $caregiverName,
                'type' => $clientType,
                'start' => $request->start,
                'end' => $request->end
            ];

            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }


        return view_component('payroll-summary-report', 'Payroll Summary Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    public function caregivers($businessId){
        $caregivers = new CaregiverDropdownResource(Caregiver::forBusinesses([$businessId])->active()->get());
        return response()->json($caregivers);
    }
}
