<?php

namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Resources\CaregiverDropdownResource;
use App\Http\Resources\ClientDropdownResource;
use App\Reports\MedicaidBillingReport;
use App\Reports\ServiceAuthUsageReport;
use Illuminate\Http\Request;

class BusinessPreCheckBillingReportController extends Controller
{
    /**
     * Get the Payroll Export Report
     *
     * @param Request $request
     * @param MedicaidBillingReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, MedicaidBillingReport $report)
    {
        if ($request->filled('json')) {
            $report = $report->forDates($request->start, $request->end, auth()->user()->role->getTimezone())
                ->forClientType($request->client_type)
                ->forClient($request->client)
                ->forCaregiver($request->caregiver);

            return response()->json($report->rows());
        }

        $clients = new ClientDropdownResource(Client::forRequestedBusinesses()->active()->get());
        $caregivers = new CaregiverDropdownResource(Caregiver::forRequestedBusinesses()->active()->get());

        return view_component(
            'business-pre-check-billing-report',
            'Pre-Check Billing Report',
            compact(['clients', 'caregivers']),
            [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
            ]
        );
    }
}