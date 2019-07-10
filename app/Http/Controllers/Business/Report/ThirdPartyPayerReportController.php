<?php

namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\Client;
use App\Billing\Payer;
use App\Http\Controllers\Controller;
use App\Http\Resources\CaregiverDropdownResource;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use App\Reports\MedicaidBillingReport;
use App\Reports\ThirdPartyPayerReport;
use Illuminate\Http\Request;

class ThirdPartyPayerReportController extends Controller
{
    /**
     * Get the Payroll Export Report
     *
     * @param Request $request
     * @param MedicaidBillingReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, ThirdPartyPayerReport $report)
    {
        if ($request->filled('json')) {


            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->caregiver_id,
                    $request->status
                );

            return response()->json($report->rows());

        }

        $clients = new ClientDropdownResource(Client::forRequestedBusinesses()->active()->get());
        $caregivers = new CaregiverDropdownResource(Caregiver::forRequestedBusinesses()->active()->get());
        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->get());

        return view_component(
            'business-medicaid-billing-report',
            'Medicaid Billing Report',
            compact(['clients', 'caregivers']),
            [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
            ]
        );
    }
}