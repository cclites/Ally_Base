<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\ClaimRemitType;
use App\Claims\Exceptions\ClaimBalanceException;
use App\Claims\Requests\UpdateClaimRemitRequest;
use App\Claims\Resources\ClaimRemitResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Requests\CreateClaimRemitRequest;
use App\Claims\Requests\GetClaimRemitsRequest;
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

            $query = ClaimRemit::forRequestedBusinesses();

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

        $init = ['remit' => new ClaimRemitResource($claimRemit)];

        return view_component('apply-remit-page', 'Apply Remit', compact('init'), [
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

        if (floatval($claimRemit->amount_applied) !== floatval(0)) {
            return new ErrorResponse(412, 'This Remit has already been applied to one or more Claims and cannot be deleted.  Please zero out the balance of this Remit and try again..');
        }

        \DB::beginTransaction();

        try {
            $updatedItems = collect([]);
            $updatedClaims = collect([]);
            // Soft-delete all related payments.
            foreach ($claimRemit->applications as $item) {
                $updatedItems->push($item->claimInvoiceItem);
                $updatedClaims->push($item->claimInvoice);
                $item->delete();
            }

            // Technically the balance should always be the same because the
            // amount applied for the remit must be 0, but we will
            // re-calculate the balances anyway to ensure no errors.
            foreach ($updatedItems->unique('id') as $item) {
                $item->updateBalance();
            }
            foreach ($updatedClaims->unique('id') as $claim) {
                $claim->updateBalance();
            }

            $claimRemit->delete();
        } catch (ClaimBalanceException $ex) {
            return new SuccessResponse('Remit could not be deleted because it has been applied to a Claim.  Error: ' . $ex->getMessage());
        }

        \DB::commit();

        return new SuccessResponse('Remit has been deleted.');
    }
}
