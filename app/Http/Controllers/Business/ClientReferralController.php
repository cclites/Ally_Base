<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ReferralSource;
use Validator;

class ClientReferralController extends BaseController
{
    public function clientReferal() {
        $referralsources = ReferralSource::all();
        return view('business.referal.clientreferallist', compact('referralsources'));
    }

    public function addClientReferal() {
        return view('business.referal.addreferal');
    }

    public function createReferralSource(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'organization' => 'required',
            'contact_name' => 'required',
            'phone' => 'required|numeric|digits_between:3,15',
        ]);

        if($validatedData->fails()) {
            return response()->json(['errors'=> $validatedData->errors()]);
        }

        if($this->business()) {
            $refsourc = new ReferralSource();
            $refsourc->fill(['business_id' => $this->business()->id]);
            $refsourc->fill($request->all());
            if($refsourc->save()) {
                return response()->json(['status' => 1, 'refsourc' => $refsourc]);
            }
        }
    }
}
