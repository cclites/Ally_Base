<?php


namespace App\Http\Controllers\Business\Report;

use App\Billing\Payer;
use App\Client;
use App\Http\Controllers\Business\BaseController;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use Illuminate\Http\Request;

class PaymentSummaryByPayerReportController extends BaseController
{
    public function index(Request $request, PaymentSummaryByPayerReport $report){

        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $this->authorize('read', Business::find($request->business));

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->client_type,
                    $request->client
                );

            $data = $report->rows();

            return response($data);
        }

        return view_component(
            'payment-summary-by-payer',
            'Payment Summary By Payer Report',
             [],
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );
    }

    /**
     * Get the clients for the Client Filter.
     *
     * @param Request $request
     * @return ClientDropdownResource
     */
    public function clients(Request $request)
    {
        $clients = Client::forRequestedBusinesses()
            ->active()
            ->ordered()
            ->get();

        return new ClientDropdownResource($clients);
    }

    public function payers(Request $request)
    {
        $payers = Payer::forAuthorizedChain()
            ->orderBy('name')
            ->get();

        return new PayersDropdownResource($payers);
    }
}