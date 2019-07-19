<?php

namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Billing\Payer;
use App\Http\Controllers\Controller;
use App\Http\Resources\CaregiverDropdownResource;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use App\Reports\MedicaidBillingReport;
use App\Reports\ThirdPartyPayerReport;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;

class ThirdPartyPayerReportController extends Controller
{
    /**
     * Get the Third Party Payer Report
     *
     * @param Request $request
     * @param ThirdPartyPayerReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, ThirdPartyPayerReport $report)
    {
        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $this->authorize('read', Business::find($request->business));

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->type,
                    $request->client,
                    $request->payer
                );

            $data = $report->rows();

            if (filled($request->print)) {
                $pdf = PDF::loadView('business.reports.print.third_party_report', compact('data', 'timezone'));
                return $pdf->download('third_party_report.pdf');
            }

            return response($data);
        }

        $clients = new ClientDropdownResource(Client::forRequestedBusinesses()->active()->get());
        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->get());

        return view_component(
            'third-party-payer',
            'Third Party Payer Report',
            compact(['clients', 'payers']),
            [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
            ]
        );
    }
}