<?php

namespace App\Http\Controllers\Business;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\BusinessChain;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\ClaimResource;

class ClaimsController extends BaseController
{
    public function index(Request $request, ClientInvoiceQuery $invoiceQuery)
    {
        if ($request->expectsJson()) {
            if ($request->filled('paid')) {
                if ($request->paid == 1) {
                    $invoiceQuery->paidInFull();
                } else if ($request->paid == 0) {
                    $invoiceQuery->notPaidInFull();
                }
                // TODO: handle paid = 2/3 (has/doesn't have claim)
            }

            $invoiceQuery->forRequestedBusinesses();

            if ($request->has('start_date')) {
                $startDate = Carbon::parse($request->start_date)->toDateTimeString();
                $endDate = Carbon::parse($request->end_date)->toDateString() . ' 23:59:59';
                $invoiceQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($request->filled('client_id')) {
                $invoiceQuery->where('client_id', $request->client_id);
            }

            if ($request->filled('payer_id')) {
                $invoiceQuery->whereHas('clientPayer', function ($query) use($request) {
                    $query->where('payer_id', $request->payer_id);
                });
            }

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments', 'claim'])->get();

            return ClaimResource::collection($invoices);
        }

        return view_component('business-claims-ar', 'Claims & AR');
    }

    public function transmitInvoice(Request $request, ClientInvoice $invoice)
    {
        if (! empty($invoice->claim)) {
            // claim already exists, re-transmit?
        }

        $claim = Claim::create([
            'client_invoice_id' => $invoice->id,
            'amount' => $invoice->amount,
            'status' => Claim::CREATED,
        ]);

        $claim->statuses()->create(['status' => Claim::CREATED]);

        // TODO: transmit code

        $claim->updateStatus(Claim::TRANSMITTED);

        return new SuccessResponse('Claim was transmitted successfully.', new ClaimResource($invoice->fresh()));
    }
}