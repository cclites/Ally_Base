<?php

namespace App\Http\Controllers\Business;

use App\Billing\Claim;
use App\Billing\ClaimService;
use App\Billing\ClaimStatus;
use App\Billing\ClientInvoice;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Client;
use App\Http\Requests\PayClaimRequest;
use App\Http\Requests\TransmitClaimRequest;
use App\Http\Requests\UpdateMissingClaimsFieldsRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Services\TellusValidationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Responses\Resources\ClaimResource;

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
                    case 'overpaid':
                        $invoiceQuery->whereHas('claim', function (Builder $q) {
                            $q->whereColumn('amount_paid', '>', 'amount');
                        });
                        break;
                    case 'paid':
                        $invoiceQuery->where(function ($q) {
                            $q->where(function ($q) {
                                $q->where('offline', false)->whereColumn('amount_paid', '=', 'amount');
                            })->orWhere(function ($q) {
                                $q->where('offline', true)->whereColumn('offline_amount_paid', '=', 'amount');
                            });
                        });
                        break;
                    case 'unpaid':
                        $invoiceQuery->where(function ($q) {
                            $q->where(function ($q) {
                                $q->where('offline', false)->whereColumn('amount_paid', '<', 'amount');
                            })->orWhere(function ($q) {
                                $q->where('offline', true)->whereColumn('offline_amount_paid', '<', 'amount');
                            });
                        });
                        break;
                    case 'has_claim':
                        $invoiceQuery->whereHas('claim');
                        break;
                    case 'no_claim':
                        $invoiceQuery->whereDoesntHave('claim');
                        break;
                    case 'has_balance':
                        $invoiceQuery->whereHas('claim', function (Builder $q) {
                            $q->whereColumn('amount', '>', 'amount_paid');
                        });
                        break;
                    case 'no_balance':
                        $invoiceQuery->whereHas('claim', function (Builder $q) {
                            $q->whereColumn('amount', '<=', 'amount_paid');
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
     * @param \Illuminate\Http\Request $request
     * @param ClientInvoice $invoice
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function transmitInvoice(Request $request, ClientInvoice $invoice)
    {
        $this->authorize('read', $invoice);

        if (! $invoice->clientPayer) {
            return new ErrorResponse(500, 'No payer assigned to this invoice, cannot transmit this claim.');
        }

        // if no transmission set on the payer, attempt to get it from the request
        if (! $service = $invoice->clientPayer->payer->getTransmissionMethod()) {
            if ($method = $request->input('method', null)) {
                $service = ClaimService::$method();
            }
        }

        if (empty($service)) {
            return new ErrorResponse(500, 'You cannot transmit this claim because the Payer for this invoice does not have a transmission method set.  You can edit this on the Billing > Payers section, or contact Ally for assistance.');
        }

        try {
            \DB::beginTransaction();

            $transmitter = Claim::getTransmitter($service);
            if ($errors = $transmitter->validateInvoice($invoice)) {
                return new ErrorResponse(412, 'Required data missing for transmitting claim.', $errors);
            }

            $claim = Claim::getOrCreate($invoice);

            if ($transmitter->isTestMode($claim)) {
                $testFile = $transmitter->test($claim);
            } else {
                $transmitter->send($claim);

                dd( $claim );
                $claim->updateStatus(ClaimStatus::TRANSMITTED(), [
                    'service' => $service,
                ]);
            }

            \DB::commit();

            $data = ['claim' => new ClaimResource($invoice->fresh())];
            if (isset($testFile)) {
                $data['test_result'] = $testFile;
            }
            return new SuccessResponse('Claim was transmitted successfully.', $data);
        } catch (TellusValidationException $ex) {
            // Handle returning list of validation errors
            return new ErrorResponse(420, 'Could not submit, error with Claim data.', ['tellus_errors' => $ex->getErrors()]);
        } catch (ClaimTransmissionException $ex) {
            return new ErrorResponse(500, $ex->getMessage());
        } catch (\Exception $ex) {
            \Log::error($ex);
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An unexpected error occurred while trying to transmit the claim.  Please try again.');
        }
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
        $this->authorize('read', $invoice);

        if (empty($invoice->claim)) {
            return new ErrorResponse(412, 'Cannot apply payment until the claim has been transmitted.');
        }

        $invoice->claim->addPayment($request->toClaimPayment());

        return new SuccessResponse('Payment was successfully applied.', new ClaimResource($invoice->fresh()));
    }

    /**
     * Update missing fields that are required for transmitting the invoice.
     *
     * @param UpdateMissingClaimsFieldsRequest $request
     * @param ClientInvoice $invoice
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function updateMissingFields(UpdateMissingClaimsFieldsRequest $request, ClientInvoice $invoice)
    {
        $businessData = [];
        $clientData = [];
        $payerData = [];
        foreach ($request->filtered() as $field => $value) {
            if (starts_with($field, 'business_')) {
                $businessData[substr($field, 9)] = $value;
            }
            if (starts_with($field, 'credentials_')) {
                $businessData[substr($field, 12)] = $value;
            }
            if (starts_with($field, 'client_')) {
                $clientData[substr($field, 7)] = $value;
            }
            if (starts_with($field, 'payer_')) {
                $payerData[substr($field, 6)] = $value;
            }
        }

        \DB::beginTransaction();

        if (! empty($businessData)) {
            $this->authorize('update', $invoice->client->business);
            app('settings')->set($invoice->client->business, $businessData);
        }

        if (! empty($clientData)) {
            $this->authorize('update', $invoice->client);
            $invoice->client->update($clientData);
        }

        if (! empty($payerData)) {
            if (empty($invoice->clientPayer)) {
                return new ErrorResponse(500, 'Invoice has no payer.');
            }

            $this->authorize('update', $invoice->clientPayer->payer);
            $invoice->clientPayer->payer->update($payerData);
        }

        \DB::commit();

        return new SuccessResponse('Required fields have been saved.  You can now transmit the invoice.', $invoice);
    }

    /**
     * Get the response results from an HHA transmission.
     *
     * @param Claim $claim
     * @return \Illuminate\Http\JsonResponse
     */
    public function hhaResults(Claim $claim)
    {
        $hhaFile = $claim->hhaFiles()->with('results')->latest()->first();

        if (empty($hhaFile)) {
            return response()->json([]);
        }

        return response()->json($hhaFile->results);
    }
}