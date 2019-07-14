<?php

namespace App\Http\Controllers\Business\Report;

//TODO: Add imports
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

        if ($request > filled('json')) {

           $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start,
                        $request->end,
                        $request->business,
                        $request->client_type,
                        $request->caregiver
                    );

            return response($report->rows());
        }

        $caregivers = new CaregiverDropdownResource(Caregiver::forRequestedBusinesses()->active()->get());

        //TODO: add any parameters, change component name, change report name
        return view_component('payroll-summary-report', 'Payroll Summary Report', compact('caregivers'), [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}
