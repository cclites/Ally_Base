<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Exceptions\ClaimTransmissionException;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ClaimsQueueResource;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Billing\ClaimService;
use App\Billing\ClaimStatus;
use App\Claims\ClaimInvoice;
use App\Services\TellusValidationException;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClaimTransmissionController extends BaseController
{
    /**
     * Create a claim from an invoice and transmit to HHAeXchange.
     *
     * @param \Illuminate\Http\Request $request
     * @param ClaimInvoice $claim
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function transmit(ClaimInvoice $claim, Request $request)
    {
        $this->authorize('update', $claim);

        // If no transmission set, attempt to get it from the request.
        if (!$service = $claim->getTransmissionMethod()) {
            if ($method = $request->input('method', null)) {
                $service = ClaimService::$method();
//                $claim->transmission_method = $method;
            }
        }

        if (empty($service)) {
            return new ErrorResponse(500, 'Error transmitting invoice: No transmission method selected.');
        }

        try {
            \DB::beginTransaction();

            $transmitter = $claim->getTransmitter($service);
            if ($errors = $transmitter->validateClaim($claim)) {
                return new ErrorResponse(412, 'Required data missing for transmitting claim.', $errors);
            }

            if ($transmitter->isTestMode($claim)) {
                $testFile = $transmitter->test($claim);
            } else {
                $transmitter->send($claim);
                if (empty($claim->transmitted_at)) {
                    $claim->update(['transmitted_at' => Carbon::now()]);
                }
                $claim->updateStatus(ClaimStatus::TRANSMITTED());
            }

            \DB::commit();

            $data = ['invoice' => new ClaimsQueueResource($claim->clientInvoice->fresh())];
            if (isset($testFile)) {
                $data['test_result'] = $testFile;
            }
            return new SuccessResponse('Claim was transmitted successfully.', $data);
        } catch (TellusValidationException $ex) {
            // Handle returning list of validation errors
            return new ErrorResponse(420, 'Could not submit because of an error with Claim XML data.', ['tellus_errors' => $ex->getErrors()]);
        } catch (ClaimTransmissionException $ex) {
            return new ErrorResponse(500, $ex->getMessage());
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An unexpected error occurred while trying to transmit the claim.  Please try again.');
        }
    }
}