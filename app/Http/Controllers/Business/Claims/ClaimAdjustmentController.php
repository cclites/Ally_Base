<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Exceptions\ClaimBalanceException;
use App\Claims\Requests\CreateClaimAdjustmentRequest;
use App\Claims\Resources\ClaimsQueueResource;
use App\Http\Controllers\Business\BaseController;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Claims\ClaimAdjustment;
use App\Claims\ClaimInvoice;

class ClaimAdjustmentController extends BaseController
{
    /**
     * Store new ClaimAdjustments.
     *
     * @param ClaimInvoice $claim
     * @param CreateClaimAdjustmentRequest $request
     * @return SuccessResponse
     * @throws \Exception
     */
    public function store(ClaimInvoice $claim, CreateClaimAdjustmentRequest $request)
    {
        $this->authorize('update', $claim);

        \DB::beginTransaction();
        try {

            $adjustments = $claim->adjustments()->createMany(
                $request->filtered()['adjustments']
            );

            $adjustments->each(function (ClaimAdjustment $adjustment) {
                $adjustment->load(['claimInvoice', 'claimInvoiceItem']);
                $adjustment->claimInvoiceItem->updateBalance();
                $adjustment->claimInvoice->updateBalance();
            });

            \DB::commit();

        } catch (ClaimBalanceException $ex) {
            return new ErrorResponse(412, $ex->getMessage());
        }

        return new SuccessResponse('An adjustment has been applied to the selected Claim.', new ClaimsQueueResource($claim->clientInvoice->fresh()));
    }
}
