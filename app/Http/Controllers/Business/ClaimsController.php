<?php

namespace App\Http\Controllers\Business;

use App\Billing\Actions\ApplyPayment;
use App\Billing\ClientInvoice;
use App\Billing\Payment;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\BusinessChain;
use App\Responses\ErrorResponse;
use App\Responses\Resources\ClientInvoice as ClientInvoiceResponse;
use App\Responses\SuccessResponse;
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

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments'])->get();

            return ClientInvoiceResponse::collection($invoices);
        }

        return view_component('business-claims-ar', 'Claims & AR');
    }

    /**
     * Apply offline payment to a ClientInvoice.
     *
     * @param Request $request
     * @param ClientInvoice $invoice
     * @param ApplyPayment $paymentApplicator
     * @return ErrorResponse|SuccessResponse
     */
    public function applyPayment(Request $request, ClientInvoice $invoice, ApplyPayment $paymentApplicator)
    {
        $request->validate([
            'payment_type' => 'required',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        try {
            $payment = Payment::create([
                'client_id' => $invoice->client_id ?? null,
                'business_id' => $invoice->client->business_id ?? null,
                'amount' => $request->amount,
                'payment_type' => 'OFFLINE',
                'notes' => $request->payment_type ?? null,
                'system_allotment' => 0.00,
                'transaction_id' => null,
                'success' => 1,
                'created_at' => utc_date($request->payment_date, 'Y-m-d H:i:s', $invoice->client->business->timezone),
            ]);

            $paymentApplicator->apply($invoice, $payment, $request->amount);
            return new SuccessResponse('Payment applied to invoice #' . $invoice->name, $invoice->fresh());
        } catch (\Exception $ex) {
            \Log::error($ex->getMessage());
        }

        return new ErrorResponse(500, 'Unexpected error while trying to apply payment.  Please try again.');
    }
}