<?php

namespace App\Http\Controllers\Business;

use App\Claims\ClaimInvoice;
use App\Claims\ClaimInvoiceItem;
use App\Http\Requests\UpdateClaimInvoiceItemRequest;
use App\Http\Resources\ClaimInvoiceResource;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;

class ClaimInvoiceItemController extends BaseController
{
    /**
     * Update the ClaimInvoiceItem.
     *
     * @param ClaimInvoice $claim
     * @param ClaimInvoiceItem $item
     * @param UpdateClaimInvoiceItemRequest $request
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ClaimInvoice $claim, ClaimInvoiceItem $item, UpdateClaimInvoiceItemRequest $request)
    {
        $this->authorize('update', $item->claim);

        try {
            \DB::beginTransaction();

            $item->claimable->update($request->getClaimableData($item->claimable_type));
            $item->update($request->getClaimItemData($item->claimable_type));

            // TODO: recalculate amount due based on applied payments for both the item and the claim

            $claim->updateBalances();

            \DB::commit();
        } catch (\Exception $ex) {
            dd($ex->getMessage());
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An unexpected error occurred while trying to update this item.  Please try again.');
        }

        return new SuccessResponse('Claim Item has been saved.', new ClaimInvoiceResource($claim->fresh()));
    }

    /**
     * Remove a ClaimInvoiceItem.
     *
     * @param ClaimInvoice $claim
     * @param ClaimInvoiceItem $item
     * @return SuccessResponse|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClaimInvoice $claim, ClaimInvoiceItem $item)
    {
        $this->authorize('update', $claim);

        try {
            \DB::beginTransaction();
            $item->delete();
            $claim->updateBalances();
            \DB::commit();
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An unexpected error occurred while trying to delete this item.  Please try again.');
        }

        return new SuccessResponse('Claim Item has been deleted.', new ClaimInvoiceResource($claim->fresh()));
    }
}
