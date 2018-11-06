<?php

namespace App\Http\Controllers\Business;

use App\ClientReferralServiceAgreement;
use App\Http\Controllers\Controller;
use App\Signature;
use Illuminate\Http\Request;

class ClientReferralServiceAgreementController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|int',
            'referral_fee' => 'required|numeric',
            'per_visit_referral_fee' => 'required|numeric',
            'per_visit_assessment_fee' => 'required|numeric',
            'termination_notice' => 'required|string',
            'executed_by' => 'required|string',
            'payment_options' => 'required|array',
            'signature_one' => 'required',
            'signature_two' => 'required',
            'signature_client' => 'required',
        ]);

        $referralServiceAgreement = ClientReferralServiceAgreement::create($data);
        return response()->json($referralServiceAgreement);
    }
}
