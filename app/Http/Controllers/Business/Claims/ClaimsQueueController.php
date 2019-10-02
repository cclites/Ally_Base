<?php

namespace App\Http\Controllers\Business\Claims;

use App\Billing\ClaimStatus;
use App\Claims\Requests\ClaimQueueRequest;
use App\ClientType;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ClaimsQueueResource;
use App\Billing\Queries\ClientInvoiceQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClaimsQueueController extends BaseController
{
    /**
     * Get claims listing.
     *
     * @param ClaimQueueRequest $request
     * @param ClientInvoiceQuery $invoiceQuery
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(ClaimQueueRequest $request, ClientInvoiceQuery $invoiceQuery)
    {
        if ($request->forJson()) {
            if ($request->filled('invoice_type')) {
                switch ($request->invoice_type) {
                    case 'paid':
                        $invoiceQuery->where(function ($q) {
                            $q->where(function ($q) {
                                $q->where('offline', 0)
                                    ->whereColumn('amount_paid', '=', 'amount');
                            })
                                ->orWhere(function ($q) {
                                    $q->where('offline', 1)
                                        ->whereColumn('offline_amount_paid', '=', 'amount');
                                });
                        });
                        break;
                    case 'unpaid':
                        $invoiceQuery->where(function ($q) {
                            $q->where(function ($q) {
                                $q->where('offline', 0)
                                    ->whereColumn('amount_paid', '!=', 'amount');
                            })
                                ->orWhere(function ($q) {
                                    $q->where('offline', 1)
                                        ->whereColumn('offline_amount_paid', '<', 'amount');
                                });
                        });
                        break;
                    case 'has_claim':
                        $invoiceQuery->whereHas('claimInvoice');
                        break;
                    case 'no_claim':
                        $invoiceQuery->whereDoesntHave('claimInvoice');
                        break;
                    case 'has_balance':
                        $invoiceQuery->whereHas('claimInvoice', function (Builder $q) {
                            $q->where('amount_due', '<>', '0');
                        });
                        break;
                    case 'no_balance':
                        $invoiceQuery->whereHas('claimInvoice', function (Builder $q) {
                            $q->where('amount_due', '=', 0);
                        });
                        break;
                }
            }

            if ($request->filled('claim_status')) {
                $status = ClaimStatus::fromValue($request->claim_status);
                $invoiceQuery->whereHas('claimInvoice', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }

            $invoiceQuery->forRequestedBusinesses();

            // Only return invoices that have a payer (adjustment invoices should not show)
            $invoiceQuery->whereNotNull('client_payer_id');

            $invoiceQuery->whereBetween('created_at', $request->filterDateRange());

            if ($request->filled('client_id')) {
                $invoiceQuery->forClient($request->client_id, false);
            }

            if ($request->filled('payer_id')) {
                $invoiceQuery->forPayer($request->payer_id);
            }

            if (in_array($request->client_type, ClientType::all())) {
                $invoiceQuery->forClientType($request->client_type);
            }

            if ($request->inactive != 1) {
                $invoiceQuery->whereHas('client', function ($q) {
                    $q->active();
                });
            }

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments', 'claimInvoice'])->get();

            $coll = ClaimsQueueResource::collection($invoices);

            return $coll;
        }

        return view_component('business-claims-queue', 'Claims Queue');
    }
}