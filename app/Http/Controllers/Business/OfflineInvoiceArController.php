<?php

namespace App\Http\Controllers\Business;

use App\Billing\Claim;
use App\Billing\ClaimService;
use App\Billing\ClaimStatus;
use App\Billing\ClientInvoice;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Billing\Queries\OfflineClientInvoiceQuery;
use App\Http\Requests\PayClaimRequest;
use App\Http\Requests\PayOfflineInvoiceRequest;
use App\Http\Requests\TransmitClaimRequest;
use App\Responses\ErrorResponse;
use App\Responses\Resources\OfflineInvoiceArResource;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Responses\Resources\ClaimResource;

class OfflineInvoiceArController extends BaseController
{
    /**
     * Get claims listing.
     *
     * @param Request $request
     * @param OfflineClientInvoiceQuery $invoiceQuery
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, OfflineClientInvoiceQuery $invoiceQuery)
    {
        if ($request->expectsJson()) {
            if ($request->filled('invoiceType')) {
                switch ($request->invoiceType) {
                    case 'paid':
                        $invoiceQuery->paidInFull();
                        break;
                    case 'unpaid':
                        $invoiceQuery->notPaidInFull();
                        break;
                    case 'overpaid':
                        $invoiceQuery->overpaid();
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
                $invoiceQuery->where('client_id', $request->client_id);
            }

            if ($request->filled('payer_id')) {
                $invoiceQuery->whereHas('clientPayer', function ($query) use($request) {
                    $query->where('payer_id', $request->payer_id);
                });
            }

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments'])->get();

            return OfflineInvoiceArResource::collection($invoices);
        }

        return view_component('business-offline-invoice-ar', 'Offline Invoice AR');
    }

    /**
     * Apply an offline 'payment' to a the Invoice.
     *
     * @param PayOfflineInvoiceRequest $request
     * @param ClientInvoice $invoice
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function pay(PayOfflineInvoiceRequest $request, ClientInvoice $invoice)
    {
        $this->authorize('read', $invoice);

        if (! $invoice->isOffline()) {
            return new ErrorResponse(400, 'Payments can only be applied to Offline Invoices.');
        }

        $invoice->addOfflinePayment($request->toOfflineInvoicePayment());

        return new SuccessResponse('Payment was successfully applied.', new OfflineInvoiceArResource($invoice->fresh()));
    }
}