<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Requests\CreateClaimRemitApplicationsRequest;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Resources\ClaimRemitResource;
use App\Responses\SuccessResponse;
use App\Claims\ClaimAdjustment;
use App\Claims\ClaimRemit;

class ClaimRemitApplicationController extends BaseController
{
    /**
     * @param ClaimRemit $claimRemit
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(ClaimRemit $claimRemit)
    {
        $this->authorize('view', $claimRemit);

        $init = ['remit' => new ClaimRemitResource($claimRemit)];

        return view_component('apply-remit-page', 'Apply Remit', compact('init'), [
            'Home' => '/',
            'Claim Remits' => route('business.claim-remits.index'),
        ]);
    }

    /**
     * Apply a remit to many ClaimInvoiceItems.
     *
     * @param ClaimRemit $claimRemit
     * @param CreateClaimRemitApplicationsRequest $request
     * @return SuccessResponse
     * @throws \Exception
     */
    public function store(ClaimRemit $claimRemit, CreateClaimRemitApplicationsRequest $request)
    {
        \DB::beginTransaction();

        $adjustments = $claimRemit->claimApplications()->createMany($request->filtered()['applications']);

        $adjustments->each(function (ClaimAdjustment $adjustment) {
            $adjustment->load(['claimInvoice', 'claimInvoiceItem']);

            if ($adjustment->is_interest) {
                return;
            }

            $adjustment->claimInvoiceItem->updateBalance();
            $adjustment->claimInvoice->updateBalance();
        });

        $claimRemit->updateBalance();

        \DB::commit();

        return new SuccessResponse('Remit has been applied to the selected Claims successfully.', null, route('business.claim-remits.index'));
    }
}
