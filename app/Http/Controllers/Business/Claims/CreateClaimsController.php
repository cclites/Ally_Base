<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Business\BaseController;
use App\Claims\Requests\GetClientInvoicesRequest;
use App\Claims\Resources\ClaimCreatorResource;
use App\Billing\Queries\ClientInvoiceQuery;

class CreateClaimsController extends BaseController
{
    /**
     * Get list of client invoices that can be created into claims.
     *
     * @param GetClientInvoicesRequest $request
     * @param ClientInvoiceQuery $invoiceQuery
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\View\View
     */
    public function index(GetClientInvoicesRequest $request, ClientInvoiceQuery $invoiceQuery)
    {
        if ($request->wantsReportData()) {
            $invoiceQuery->with(['client', 'clientPayer.payer', 'claimInvoices'])
                ->forRequestedBusinesses()
                ->whereNotNull('client_payer_id') // hides adjustment invoices
                ->forDateRange($request->filterDateRange())
                ->when($request->client_id, function (ClientInvoiceQuery $invoiceQuery, $var) {
                    $invoiceQuery->forClient($var, false);
                })
                ->when($request->payer_id, function (ClientInvoiceQuery $invoiceQuery, $var) {
                    $invoiceQuery->forPayer($var);
                })
                ->when($request->client_type, function (ClientInvoiceQuery $invoiceQuery, $var) {
                    $invoiceQuery->forClientType($var);
                })
                ->when($request->inactive != 1, function (ClientInvoiceQuery $invoiceQuery) {
                    $invoiceQuery->forActiveClientsOnly();
                })
                ->when($request->invoice_id, function (ClientInvoiceQuery $invoiceQuery, $var) {
                    $invoiceQuery->searchForId($var);
                });

            switch ($request->invoice_type) {
                case 'paid':
                    $invoiceQuery->paidInFull();
                    break;
                case 'unpaid':
                    $invoiceQuery->notPaidInFull();
                    break;
                case 'has_claim':
                    $invoiceQuery->hasClaim(false);
                    break;
                case 'no_claim':
                    $invoiceQuery->doesNotHaveClaim();
                    break;
            }

            $invoices = ClaimCreatorResource::collection($invoiceQuery->get());

            return $invoices;
        }

        return view_component('business-create-claims-page', 'Create Claims', [], [
            'Home' => '/',
            'Claims' => '#',
        ]);
    }
}