<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Requests\CreateClaimAdjustmentRequest;
use App\Claims\Resources\ClaimAdjustmentResource;
use App\Claims\Resources\ClaimInvoiceResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Exceptions\ClaimBalanceException;
use App\Claims\Resources\ClaimsQueueResource;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Claims\ClaimAdjustment;
use App\Claims\ClaimInvoice;

class ClaimAdjustmentController extends BaseController
{
    /**
     * Get the Claim Adjustment History page.
     *
     * @param ClaimInvoice $claim
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(ClaimInvoice $claim)
    {
        $this->authorize('view', $claim);

        $init = [
            'claim' => new ClaimInvoiceResource($claim),
            'adjustments' => ClaimAdjustmentResource::collection($claim->adjustments),
        ];

        return view_component('claim-adjustment-history', 'Claim Adjustment History', compact('init'), [
            'Home' => '/',
            'Claims Queue' => route('business.claims-queue'),
        ]);
    }

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
