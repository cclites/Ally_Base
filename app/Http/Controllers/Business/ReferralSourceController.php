<?php

namespace App\Http\Controllers\Business;

use App\ReferralSource;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ReferralSourceController extends BaseController
{
    public function index($edit = 0, $create = 0)
    {
        $referralsources = $this->business()->referralSources;

        return view('business.referral.list', compact('referralsources', 'edit', 'create'));
    }

    public function create()
    {
        return $this->index(0, true);
    }

    public function edit(ReferralSource $referralSource)
    {
        return $this->index($referralSource->id);
    }

    public function show(ReferralSource $referralSource)
    {
        return $this->index($referralSource->id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization' => 'required',
            'contact_name' => 'required',
            'phone' => 'nullable|max:32',
        ]);

        $referralSource = $this->business()->referralSources()->create($data);
        if ($referralSource) {
            return new CreatedResponse('The referral source has been created!', $referralSource);
        }
    }

    public function update(ReferralSource $referralSource, Request $request)
    {
        if ($referralSource->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access.');
        }

        $data = $request->validate([
            'organization' => 'required',
            'contact_name' => 'required',
            'phone' => 'nullable|max:32',
        ]);

        if ($referralSource->update($data)) {
            return new SuccessResponse('The referral source has been saved!', $referralSource);
        }
    }
}
