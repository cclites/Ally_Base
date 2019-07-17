<?php

namespace App\Http\Controllers\Business\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Business\BaseController;
use App\Reports\ClientReferralsReport;
use App\Http\Resources\ClientDropdownResource;
use App\Client;
use App\Business;
use App\User;

use Log;

/**
 *
 * @package App\Http\Controllers\Business\Report
 */
class ClientReferralsReportController extends BaseController
{

    /**
     * @param Request
     * @param PayerInvoiceReport
     */
    public function index(Request $request, ClientReferralsReport $report ){

        if ($request->filled('json')) {
            $timezone = auth()->user()->role->getTimezone();
            $result = $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->client,
                    $request->county
                );
           return $result->rows();
        }

        return view_component('client-referrals-report', 'Client Referrals', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    public function populateDropdown($business)
    {
        $clients = new ClientDropdownResource(Client::forBusinesses([$business])
            ->whereNotNull('referral_source_id')
            ->ordered()
            ->get());

        return response()->json($clients);
    }
}
