<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\ClientOnboarding;
use App\ClientReferralServiceAgreement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'signature_one_text' => 'required|string',
            'signature_two' => 'required',
            'signature_two_text' => 'required|string',
            'signature_client' => 'required',
            'onboarding_step' => 'int'
        ]);

        unset($data['onboarding_step']);
        $referralServiceAgreement = DB::transaction(function () use ($data, $request) {
            $client = Client::find($request->client_id);
            $client->update(['onboarding_step' => $request->onboarding_step]);
            return ClientReferralServiceAgreement::create($data);
        });

        $onboarding = ClientOnboarding::with(
            'activities',
            'signature',
            'client.medications',
            'client.business',
            'client.referralServiceAgreement'
        )
            ->where('client_id', $request->client_id)
            ->first();
        return response()->json(compact('onboarding'));
    }

    public function agreementPdf(ClientReferralServiceAgreement $rsa)
    {
        return response()->file(storage_path($rsa->agreement_file));
    }
}
