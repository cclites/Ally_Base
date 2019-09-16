<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Exceptions\ClaimBalanceException;
use App\Claims\Requests\UpdateClaimInvoiceItemRequest;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Validation\ValidationException;
use App\Claims\Resources\ClaimInvoiceResource;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimInvoice;

class ClaimInvoiceItemController extends BaseController
{
    /**
     * Create a new ClaimInvoiceItem.
     *
     * @param ClaimInvoice $claim
     * @param UpdateClaimInvoiceItemRequest $request
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ClaimInvoice $claim, UpdateClaimInvoiceItemRequest $request)
    {
        $this->authorize('update', $claim);

        try {
            \DB::beginTransaction();

            switch ($request->claimable_type) {
                case ClaimableService::class:
                    $claimable = ClaimableService::create($request->getClaimableData());
                    break;
                case ClaimableExpense::class:
                    $claimable = ClaimableExpense::create($request->getClaimableData());
                    break;
            }

            $item = $claim->items()->create(array_merge($request->getClaimItemData(), [
                'claimable_type' => $request->claimable_type,
                'claimable_id' => $claimable->id,
                'amount_due' => 0.00,
            ]));

            $item->updateBalance();
            $claim->updateBalance();
            $claim->markAsModified();

            \DB::commit();
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An unexpected error occurred while trying to create this item.  Please try again.');
        }

        return new SuccessResponse('Claim Item has been created.', new ClaimInvoiceResource($claim->fresh()));
    }

    /**
     * Update the ClaimInvoiceItem.
     *
     * @param ClaimInvoice $claim
     * @param ClaimInvoiceItem $item
     * @param UpdateClaimInvoiceItemRequest $request
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws ValidationException
     */
    public function update(ClaimInvoice $claim, ClaimInvoiceItem $item, UpdateClaimInvoiceItemRequest $request)
    {
        // This method validates amounts based on remits/adjustments during
        // the updateBalance() call.  This method throws a ClaimBalanceException
        // if the amount is less than what was applied.

        $this->authorize('update', $claim);

        try {
            \DB::beginTransaction();

            $item->claimable->update($request->getClaimableData());
            $item->update($request->getClaimItemData());

            $item->updateBalance();
            $claim->updateBalance();
            $claim->markAsModified();

            \DB::commit();
        } catch (ClaimBalanceException $ex) {
            return new ErrorResponse(500, 'Could not update Claim Item.  When changing the total cost of an item, it cannot be less than what has already been applied.');
        } catch (ValidationException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
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

        if ($item->remitApplications()->count() > 0) {
            return new ErrorResponse(412, 'Could not delete this Claim Item because it has one or more remits applied to it.');
        }

        try {
            \DB::beginTransaction();
            $item->delete();
            $claim->updateBalance();
            $claim->markAsModified();
            \DB::commit();
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return new ErrorResponse(500, 'An unexpected error occurred while trying to delete this item.  Please try again.');
        }

        return new SuccessResponse('Claim Item has been deleted.', new ClaimInvoiceResource($claim->fresh()));
    }
}
