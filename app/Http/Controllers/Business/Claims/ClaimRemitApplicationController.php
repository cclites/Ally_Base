<?php
namespace App\Http\Controllers\Business\Claims;


use App\Claims\Requests\CreateClaimRemitApplicationsRequest;
use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimRemitApplication;
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

        $applications = $claimRemit->applications()->createMany($request->filtered()['applications']);

        $applications->each(function (ClaimRemitApplication $application) {
            $application->load(['claimInvoice', 'claimInvoiceItem']);

            if ($application->is_interest) {
                return;
            }

            $application->claimInvoiceItem->updateBalance();
            $application->claimInvoice->updateBalances();
        });

        $claimRemit->updateBalance();

        \DB::commit();

        return new SuccessResponse('Remit has been applied to the selected Claims successfully.', null, route('business.claim-remits.index'));
    }
}