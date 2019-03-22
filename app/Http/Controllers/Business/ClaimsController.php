<?php

namespace App\Http\Controllers\Business;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\BusinessChain;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\ClaimResource;
use App\Services\HhaExchangeManager;

class ClaimsController extends BaseController
{
    public function index(Request $request, ClientInvoiceQuery $invoiceQuery)
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
                    case 'has_claim':
                        $invoiceQuery->whereHasClaim();
                        break;
                    case 'no_claim':
                        $invoiceQuery->whereNoClaim();
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

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments', 'claim'])->get();

            return ClaimResource::collection($invoices);
        }

        return view_component('business-claims-ar', 'Claims & AR');
    }

    /**
     * Create a claim from an invoice and transmit to HHAeXchange.
     *
     * @param Request $request
     * @param ClientInvoice $invoice
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function transmitInvoice(Request $request, ClientInvoice $invoice)
    {
        // TODO: validation
        if (empty($invoice->client->business->ein) || empty($invoice->client->business->medicaid_id)) {
            return new ErrorResponse(412, 'Business does not have the required data to submit claims.');
        }

        $claim = $invoice->claim;
        if (empty($claim)) {
            $claim = Claim::create([
                'client_invoice_id' => $invoice->id,
                'amount' => $invoice->amount,
                'status' => Claim::CREATED,
            ]);

            $claim->statuses()->create(['status' => Claim::CREATED]);
        }

        $shiftData = $claim->getHhaExchangeData();
        if (empty($shiftData)) {
            return new ErrorResponse(412, 'You cannot create a claim because there are no shifts attached to this invoice.');
        }

        $hha = new HhaExchangeManager($invoice->client->business->ein);
        $hha->addItems($shiftData);
        if ($hha->uploadCsv()) {
            $claim->updateStatus(Claim::TRANSMITTED);
            return new SuccessResponse('Claim was transmitted successfully.', new ClaimResource($invoice->fresh()));
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to transmit the claim.  Please try again.');
    }
}