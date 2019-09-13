<?php

namespace App\Http\Controllers\Business\Claims;

use App\Billing\Claim;
use App\Billing\ClaimService;
use App\Billing\ClaimStatus;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Claims\ClaimInvoice;
use App\Http\Controllers\Business\BaseController;
use App\Responses\Resources\ClaimResource;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Billing\ClientInvoice;
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
        if (! $service = $claim->getTransmissionMethod()) {
            if ($method = $request->input('method', null)) {
                $service = ClaimService::$method();
                $claim->transmission_method = $method;
            }
        }

        if (empty($service)) {
            return new ErrorResponse(500, 'Error transmitting invoice: No transmission method selected.');
        }

//        try {
//            \DB::beginTransaction();
//
//            $transmitter = Claim::getTransmitter($service);
//            if ($errors = $transmitter->validateInvoice($invoice)) {
//                return new ErrorResponse(412, 'Required data missing for transmitting claim.', $errors);
//            }
//
//            $claim = Claim::getOrCreate($invoice);
//
//            if ($transmitter->isTestMode($claim)) {
//                $testFile = $transmitter->test($claim);
//            } else {
//                $transmitter->send($claim);
//                $claim->updateStatus(ClaimStatus::TRANSMITTED(), [
//                    'service' => $service,
//                ]);
//            }
//
//            \DB::commit();
//
//            $data = ['claim' => new ClaimResource($invoice->fresh())];
//            if (isset($testFile)) {
//                $data['test_result'] = $testFile;
//            }
//            return new SuccessResponse('Claim was transmitted successfully.', $data);
//        } catch (ClaimTransmissionException $ex) {
//            return new ErrorResponse(500, $ex->getMessage());
//        } catch (\Exception $ex) {
//            \Log::error($ex);
//            app('sentry')->captureException($ex);
//            return new ErrorResponse(500, 'An unexpected error occurred while trying to transmit the claim.  Please try again.');
//        }
    }

}