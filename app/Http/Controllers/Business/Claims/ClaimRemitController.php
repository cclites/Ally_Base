<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Resources\RemitApplicationHistoryResource;
use App\Claims\Resources\ClaimAdjustmentResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Requests\UpdateClaimRemitRequest;
use App\Claims\Requests\CreateClaimRemitRequest;
use App\Claims\Resources\ClaimInvoiceResource;
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateClaimRemitRequest $request)
    {
        $this->authorize('create', [ClaimRemit::class, $request->filtered()]);

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

        $adjustments = $claimRemit->adjustments()
            ->with([
                'claimInvoice',
                'claimInvoice.clientInvoices.client',
                'claimInvoice.items.clientInvoice',
                'claimInvoice.client',
                'claimInvoice.payer',
                'claimInvoiceItem',
                'claimInvoiceItem.clientInvoice',
                'claimInvoiceItem.claim.business',
            ])
            ->orderBy('created_at')
            ->get();

        $applications = $adjustments->where('is_interest', false)
            ->where('claim_invoice_id', '<>', null)
            ->groupBy('claim_invoice_id');


        $fixed = [];
        foreach ($applications as $key => $items) {
            $fixed[$key] = array_merge((new ClaimInvoiceResource($items->first()->claimInvoice))->toArray(request()), [
                'items' => ClaimAdjustmentResource::collection($items)->toArray(request()),
            ]);
        }

        $history = collect([
            'interest' => $adjustments->where('is_interest', true)->values(),
            'adjustments' => $adjustments->where('is_interest', false)
                ->where('claim_invoice_id', '=', null)->values(),
            'applications' => array_values($fixed)
        ]);

        $init = [
            'remit' => new ClaimRemitResource($claimRemit),
            'adjustments' => $history,
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

        $claimRemit->delete();

        return new SuccessResponse('Remit has been deleted.');
    }
}
