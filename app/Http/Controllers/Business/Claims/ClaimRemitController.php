<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Requests\UpdateClaimRemitRequest;
use App\Claims\Resources\ClaimAdjustmentResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Requests\CreateClaimRemitRequest;
use App\Claims\Requests\GetClaimRemitsRequest;
use App\Claims\Resources\ClaimRemitResource;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Claims\ClaimRemit;

class ClaimRemitController extends BaseController
{
    /**
     * Get a list of ClaimRemits.
     *
     * @param GetClaimRemitsRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(GetClaimRemitsRequest $request)
    {
        if ($request->forJson()) {
            $filters = $request->filtered();

            $query = ClaimRemit::with('business')
                ->withCount('adjustments')
                ->forRequestedBusinesses();

            if ($filters['all']) {
                // Show all with a balance.
                $query->whereColumn('amount', '<>', 'amount_applied');
            } else {
                $query->forDateRange($filters['start_date'], $filters['end_date'])
                    ->forPayer($filters['payer_id'])
                    ->withReferenceId($filters['reference'])
                    ->withType($filters['type'])
                    ->withStatus($filters['status']);
            }

            $results = $query->get();

            return ClaimRemitResource::collection($results);
        }

        return view_component(
            'claim-remits',
            'Claim Remits'
        );
    }

    /**
     * Create a new ClaimRemit.
     *
     * @param CreateClaimRemitRequest $request
     * @return ErrorResponse|SuccessResponse
     */
    public function store(CreateClaimRemitRequest $request)
    {
        if ($remit = ClaimRemit::create($request->filtered())) {
            return new SuccessResponse('Remit has been created.', new ClaimRemitResource($remit));
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create a Remit.  Please try again.');
    }

    /**
     * Update a ClaimRemit.
     *
     * @param UpdateClaimRemitRequest $request
     * @param ClaimRemit $claimRemit
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateClaimRemitRequest $request, ClaimRemit $claimRemit)
    {
        $this->authorize('update', $claimRemit);

        $claimRemit->update($request->filtered());

        return new SuccessResponse('Remit has been updated.', new ClaimRemitResource($claimRemit->fresh()));
    }

    /**
     * Show the Claim Remit page.
     *
     * @param ClaimRemit $claimRemit
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ClaimRemit $claimRemit)
    {
        $this->authorize('view', $claimRemit);

        $init = [
            'remit' => new ClaimRemitResource($claimRemit),
            'adjustments' => ClaimAdjustmentResource::collection($claimRemit->adjustments),
        ];

        return view_component('remit-application-history', 'Remit Application History', compact('init'), [
            'Home' => '/',
            'Claim Remits' => route('business.claim-remits.index'),
        ]);
    }

    /**
     * Delete a ClaimRemit.
     *
     * @param ClaimRemit $claimRemit
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(ClaimRemit $claimRemit)
    {
        $this->authorize('update', $claimRemit);

        if ($claimRemit->adjustments()->exists()) {
            return new ErrorResponse(412, 'This Remit has already been applied or adjusted and cannot be deleted.');
        }

        \DB::beginTransaction();

        // Keeping this in case we bring this feature back
//        $updatedItems = collect([]);
//        $updatedClaims = collect([]);
//        // Soft-delete all related payments.
//        foreach ($claimRemit->adjustments as $item) {
//            if (filled($item->claimInvoiceItem)) {
//                $updatedItems->push($item->claimInvoiceItem);
//            }
//            if (filled($item->claimInvoice)) {
//                $updatedClaims->push($item->claimInvoice);
//            }
//            $item->delete();
//        }
//
//        // Technically the balance should always be the same because the
//        // amount applied for the remit must be 0, but we will
//        // re-calculate the balances anyway to ensure no errors.
//        foreach ($updatedItems->unique('id') as $item) {
//            $item->updateBalance();
//        }
//        foreach ($updatedClaims->unique('id') as $claim) {
//            $claim->updateBalance();
//        }
//
        $claimRemit->delete();

        \DB::commit();

        return new SuccessResponse('Remit has been deleted.');
    }
}
