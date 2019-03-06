<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\BusinessChain;
use App\Responses\Resources\ClientInvoice as ClientInvoiceResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClaimsController extends BaseController
{
    public function index(Request $request, ClientInvoiceQuery $invoiceQuery)
    {
        if ($request->expectsJson()) {
            if ($request->filled('paid')) {
                if ($request->paid) {
                    $invoiceQuery->paidInFull();
                } else {
                    $invoiceQuery->notPaidInFull();
                }
            }

//            if ($businessId = $request->input('business_id')) {
//                $invoiceQuery->forBusiness($businessId);
//            }
            $invoiceQuery->forRequestedBusinesses();

            if ($request->has('start_date')) {
                $startDate = Carbon::parse($request->start_date)->toDateTimeString();
                $endDate = Carbon::parse($request->end_date)->toDateString() . ' 23:59:59';
                $invoiceQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments'])->get();

            return ClientInvoiceResponse::collection($invoices);
        }

        return view_component('business-claims-ar', 'Claims & AR');
    }
}