<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\ClaimInvoiceItem;
use App\Claims\Requests\AdjustAllClaimItemsRequest;
use App\Claims\Requests\CreateClaimAdjustmentRequest;
use App\Claims\Resources\ClaimAdjustmentResource;
use App\Claims\Resources\ClaimInvoiceResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ManageClaimsResource;
use App\Responses\SuccessResponse;
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
            'Manage Claims' => route('business.claims-manager'),
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

        $adjustments = $claim->adjustments()->createMany(
            $request->filtered()['adjustments']
        );

        $adjustments->each(function (ClaimAdjustment $adjustment) {
            $adjustment->load(['claimInvoice', 'claimInvoiceItem']);
            $adjustment->claimInvoiceItem->updateBalance();
            $adjustment->claimInvoice->updateBalance();
        });

        \DB::commit();

        return new SuccessResponse('An adjustment has been applied to the selected Claim.', new ManageClaimsResource($claim->fresh()));
    }

    /**
     * Adjust all items equally based on submitted percentage.
     *
     * @param ClaimInvoice $claim
     * @param AdjustAllClaimItemsRequest $request
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function adjustAll(ClaimInvoice $claim, AdjustAllClaimItemsRequest $request)
    {
        $this->authorize('update', $claim);

        $data = $request->filtered();

        \DB::beginTransaction();

        $claim->items()->each(function (ClaimInvoiceItem $item) use ($claim, $data) {
            $percentage = float ()
            $claim->adjustments()->create([$data]);

            $item->updateBalance();
        });

        $claim->updateBalance();

        \DB::commit();

        return new SuccessResponse('An adjustment has been applied to the selected Claim.', new ManageClaimsResource($claim->fresh()));
    }
}
