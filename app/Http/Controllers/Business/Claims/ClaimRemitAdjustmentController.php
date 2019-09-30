<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Requests\CreateClaimRemitAdjustmentRequest;
use App\Claims\Resources\ClaimRemitAdjustmentResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimRemitAdjustment;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;

class ClaimRemitAdjustmentController extends BaseController
{
    /**
     * Create a new ClaimRemitAdjustment.
     *
     * @param CreateClaimRemitAdjustmentRequest $request
     * @return ErrorResponse|SuccessResponse
     */
    public function store(CreateClaimRemitAdjustmentRequest $request)
    {
        if ($remit = ClaimRemitAdjustment::create($request->filtered())) {
            return new SuccessResponse('Remit has been adjusted.', new ClaimRemitAdjustmentResource($remit));
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create the adjustment.  Please try again.');
    }
}
