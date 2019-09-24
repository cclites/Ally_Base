<?php

namespace App\Http\Controllers\Business\Claims;

use App\Billing\Service;
use App\Http\Controllers\Business\BaseController;
use App\Claims\ClaimInvoice;
use App\Caregiver;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClaimResourceController extends BaseController
{
    /**
     *
     *
     * @param ClaimInvoice $claim
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function caregivers(ClaimInvoice $claim)
    {
        $this->authorize('read', $claim);

        $caregivers = Caregiver::forBusinesses([$claim->business_id])
            ->ordered()
            ->get()
            ->map(function ($caregiver) {
                /** @var \App\Caregiver $caregiver */
                return [
                    'id' => $caregiver->id,
                    'name_last_first' => $caregiver->name_last_first,
                    'first_name' => $caregiver->first_name,
                    'last_name' => $caregiver->last_name,
                    'gender' => $caregiver->gender,
                    'date_of_birth' => Carbon::parse($caregiver->date_of_birth)->format('m/d/Y'),
                    'medicaid_id' => $caregiver->medicaid_id,
                ];
            });

        return response()->json($caregivers);
    }

    public function services(ClaimInvoice $claim)
    {
        $services = Service::forChains([$claim->business->chain_id])
            ->ordered()
            ->get()
            ->map(function ($service) {
                /** \App\Billing\Service $service */
                return []
            });
    }
}
