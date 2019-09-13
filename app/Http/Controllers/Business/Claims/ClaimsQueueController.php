<?php

namespace App\Http\Controllers\Business\Claims;

use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ClaimsQueueResource;
use Illuminate\Database\Eloquent\Builder;
use App\Billing\Queries\ClientInvoiceQuery;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClaimsQueueController extends BaseController
{
    /**
     * Get claims listing.
     *
     * @param Request $request
     * @param ClientInvoiceQuery $invoiceQuery
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, ClientInvoiceQuery $invoiceQuery)
    {
        if ($request->filled('json') || $request->expectsJson()) {
            if ($request->filled('invoiceType')) {
                switch ($request->invoiceType) {
                    case 'paid':
                        $invoiceQuery->paidInFull();
                        break;
                    case 'unpaid':
                        $invoiceQuery->notPaidInFull();
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

            $invoiceQuery->forRequestedBusinesses();

            if ($request->has('start_date')) {
                $startDate = Carbon::parse($request->start_date)->toDateTimeString();
                $endDate = Carbon::parse($request->end_date)->toDateString() . ' 23:59:59';
                $invoiceQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($request->filled('client_id')) {
                $invoiceQuery->forClient($request->client_id);
            }

            if ($request->filled('payer_id')) {
                $invoiceQuery->forPayer($request->payer_id);
            }

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments', 'claimInvoice'])->get();
            $coll = ClaimsQueueResource::collection($invoices);

            return $coll;
        }

        return view_component('business-claims-queue', 'Claims Queue');
    }
}