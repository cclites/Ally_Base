<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Exceptions\ClaimTransmissionException;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ManageClaimsResource;
use App\Services\TellusValidationException;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Claims\ClaimService;
use App\Claims\ClaimStatus;
use App\Claims\ClaimInvoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        if (! $service = $claim->getTransmissionMethod()) {
            if ($method = $request->input('method', null)) {
                $service = ClaimService::$method();
            }
        }

        if (empty($service)) {
            // It is possible that the method was changed to null in another tab
            // and the user was never prompted to select the service.  We should
            // return a response to trigger that select service modal be shown.
            $data = ['invoice' => new ManageClaimsResource($claim->fresh())];
            return new ErrorResponse(501, 'Error transmitting invoice: No transmission method selected.', $data);
        }

        try {
            \DB::beginTransaction();

            $claim->update(['transmission_method' => $service]);

            $transmitter = $claim->getTransmitter($service);

            if ($reason = $transmitter->prevent($claim)) {
                return new ErrorResponse(400, "Could not transmit claim: $reason");
            }

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

            $data = ['invoice' => new ManageClaimsResource($claim->fresh())];
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
            \Log::info($ex);
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An unexpected error occurred while trying to transmit the claim.  Please try again.');
        }
    }
}