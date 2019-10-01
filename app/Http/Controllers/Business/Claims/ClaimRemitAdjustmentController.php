<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\ClaimAdjustment;
use App\Claims\ClaimRemit;
use App\Claims\Requests\CreateClaimRemitAdjustmentRequest;
use App\Claims\Resources\ClaimRemitAdjustmentResource;
use App\Claims\Resources\ClaimRemitResource;
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
     * @param ClaimRemit $claimRemit
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateClaimRemitAdjustmentRequest $request, ClaimRemit $claimRemit)
    {
        $this->authorize('update', $claimRemit);

        \DB::beginTransaction();

        if ($claimRemit->adjustments()->create($request->filtered())) {
            $claimRemit->updateBalance();

            \DB::commit();
            return new SuccessResponse('Remit has been adjusted.', new ClaimRemitResource($claimRemit->fresh()));
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create the adjustment.  Please try again.');
    }
}
