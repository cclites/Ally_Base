<?php

namespace App\Http\Controllers\Business;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\BusinessChain;
use App\Http\Requests\PayClaimRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\ClaimResource;
use App\Services\HhaExchangeManager;

class ClaimsController extends BaseController
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
                        $invoiceQuery->whereHas('claim');
                        break;
                    case 'no_claim':
                        $invoiceQuery->whereDoesntHave('claim');
                        break;
                    case 'has_balance':
                        $invoiceQuery->whereHas('claim', function ($q) {
                            $q->where('balance', '<>', 0.0);
                        });
                        break;
                    case 'no_balance':
                        $invoiceQuery->whereHas('claim', function ($q) {
                            $q->where('balance', '=', 0.0);
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
        $this->authorize('read', $invoice);

        if (empty($invoice->client->business->ein)) {
            return new ErrorResponse(412, 'You cannot submit a claim because you do not have an EIN set.  You can edit this information under Settings > General > Medicaid.');
        }
        if (empty($invoice->client->medicaid_id)) {
            return new ErrorResponse(412, 'You cannot submit a claim because the client does not have a Medicaid ID set.  You can edit this information under the Insurance & Service Auths section of the Client\'s profile.');
        }
        if (empty($invoice->client->business->hha_username) || empty($invoice->client->business->getHhaPassword())) {
            return new ErrorResponse(412, 'You cannot submit a claim because you do not have your HHAeXchange credentials set up.  You can edit this information under Settings > General > Claims, or contact Ally for assistance.');
        }

        $claim = $invoice->claim;
        if (empty($claim)) {
            $claim = Claim::create([
                'client_invoice_id' => $invoice->id,
                'amount' => $invoice->amount,
                'balance' => $invoice->amount,
                'status' => Claim::CREATED,
            ]);

            $claim->statuses()->create(['status' => Claim::CREATED]);
        }

        $shiftData = $claim->getHhaExchangeData();
        if (empty($shiftData)) {
            return new ErrorResponse(412, 'You cannot create a claim because there are no shifts attached to this invoice.');
        }

        try {
            $hha = new HhaExchangeManager(
                $invoice->client->business->hha_username,
                $invoice->client->business->getHhaPassword(),
                $invoice->client->business->ein
            );
        } catch (\Exception $ex) {
            return new ErrorResponse(500, 'Unable to login to HHAeXchange SFTP server.  Please check your credentials and try again.');
        }

        $hha->addItems($shiftData);
        if ($hha->uploadCsv()) {
            $claim->updateStatus(Claim::TRANSMITTED);
            return new SuccessResponse('Claim was transmitted successfully.', new ClaimResource($invoice->fresh()));
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to transmit the claim.  Please try again.');
    }

    /**
     * Apply payment to a the Invoice's claim.
     *
     * @param PayClaimRequest $request
     * @param ClientInvoice $invoice
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function pay(PayClaimRequest $request, ClientInvoice $invoice)
    {
        if (empty($invoice->claim)) {
            return new ErrorResponse(412, 'Cannot apply payment until the claim has been transmitted.');
        }

        if (floatval($request->amount) > floatval($invoice->claim->balance)) {
            return new ErrorResponse(412, 'This payment amount exceeds the claim balance.  Please modify the payment amount and try again.');
        }

        \DB::beginTransaction();

        $invoice->claim->payments()->create($request->filtered());

        $invoice->claim->recalculateBalance();

        \DB::commit();

        return new SuccessResponse('Payment was successfully applied.', new ClaimResource($invoice->fresh()));
    }
}