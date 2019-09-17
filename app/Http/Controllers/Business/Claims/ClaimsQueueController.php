<?php

namespace App\Http\Controllers\Business\Claims;

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

            $invoiceQuery->forRequestedBusinesses();

            // Only return invoices that have a payer (adjustment invoices should not show)
            $invoiceQuery->whereNotNull('client_payer_id');

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