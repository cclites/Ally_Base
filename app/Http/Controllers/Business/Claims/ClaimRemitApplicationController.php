<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Requests\CreateClaimRemitApplicationsRequest;
use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimAdjustment;
use App\Responses\SuccessResponse;
use App\Claims\ClaimRemit;

class ClaimRemitApplicationController extends BaseController
{
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

        $adjustments = $claimRemit->adjustments()->createMany($request->filtered()['applications']);

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
