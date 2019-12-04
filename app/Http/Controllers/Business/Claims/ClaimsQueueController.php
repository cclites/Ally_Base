<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ClaimsQueueResource;
use App\Claims\Requests\ClaimQueueRequest;
use App\Claims\Queries\ClaimInvoiceQuery;

class ClaimsQueueController extends BaseController
{
    /**
     * Get claims listing.
     *
     * @param ClaimQueueRequest $request
     * @param ClaimInvoiceQuery $claimQuery
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(ClaimQueueRequest $request, ClaimInvoiceQuery $claimQuery)
    {
        if ($request->forJson()) {
            $filters = $request->filtered();

            $claimQuery->with('clientInvoices.client')
                ->forRequestedBusinesses()
                ->when($filters['balance'] == 'has_balance', function (ClaimInvoiceQuery $q) {
                    $q->notPaidInFull();
                })
                ->when($filters['balance'] == 'no_balance', function (ClaimInvoiceQuery $q) {
                    $q->paidInFull();
                })
                ->when(! $filters['inactive'], function (ClaimInvoiceQuery $q) {
                    $q->forActiveClientsOnly();
                })
                ->when($filters['claim_type'], function (ClaimInvoiceQuery $q, $var) {
                    $q->withType($var);
                })
                ->when($filters['claim_status'], function (ClaimInvoiceQuery $q, $var) {
                    $q->withStatus($var);
                })
                ->when($filters['client_id'], function (ClaimInvoiceQuery $q, $var) {
                    $q->forClient($var);
                })
                ->when(filled($filters['payer_id']), function (ClaimInvoiceQuery $q) use ($filters) {
                    $q->forPayer($filters['payer_id']);
                })
                ->when($filters['client_type'], function (ClaimInvoiceQuery $q, $var) {
                    $q->forClientType($var);
                })
                ->when($filters['invoice_id'], function (ClaimInvoiceQuery $q, $var) {
                    $q->searchForInvoiceId($var);
                });

            if ($request->getDateSearchType() == 'invoice') {
                $claimQuery->whereInvoicedBetween($request->filterDateRange());
            } else {
                $claimQuery->whereDatesOfServiceBetween($request->filterDateRange());
            }

            return ClaimsQueueResource::collection($claimQuery->get());
        }

        return view_component('business-claims-queue', 'Manage Claims', [], [
            'Home' => '/',
            'Claims' => '#',
        ]);
    }
}