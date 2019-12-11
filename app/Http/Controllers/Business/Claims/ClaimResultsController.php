<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Resources\ClaimTransmissionFileResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimInvoice;

class ClaimResultsController extends BaseController
{
    /**
     * Get the response results from an HHA transmission.
     *
     * // TODO: implement Tellus results
     *
     * @param ClaimInvoice $claim
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ClaimInvoice $claim)
    {
        return response()->json(
            new ClaimTransmissionFileResource($claim->getLatestTransmissionFile())
        );
    }
}