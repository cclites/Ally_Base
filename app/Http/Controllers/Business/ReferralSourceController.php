<?php
namespace App\Http\Controllers\Business;

use App\Http\Requests\UpdateReferralSourceRequest;
use App\ReferralSource;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;

class ReferralSourceController extends BaseController
{
    public function index($edit = 0, $create = 0)
    {
        $referralsources = ReferralSource::forRequestedBusinesses()->ordered()->get();
        if (request()->expectsJson()) {
            return $referralsources;
        }

        return view('business.referral.list', compact('referralsources', 'edit', 'create'));
    }

    public function create()
    {
        return $this->index(0, true);
    }

    public function edit(ReferralSource $referralSource)
    {
        $this->authorize('update', $referralSource);

        return $this->index($referralSource->id);
    }

    public function show(ReferralSource $referralSource)
    {
        $this->authorize('read', $referralSource);

        $referralSource->load([
            'notes.creator',
            'notes' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            },
        ]);

        $business = $this->business();

        return view('business.referral.show', compact('referralSource', 'business'));
    }

    public function store(UpdateReferralSourceRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [ReferralSource::class, $data]);

        $referralSource = ReferralSource::create($data);
        if ($referralSource) {
            return new CreatedResponse('The referral source has been created!', $referralSource);
        }

        return new ErrorResponse(500, 'Unable to create referral source.');
    }

    public function update(ReferralSource $referralSource, UpdateReferralSourceRequest $request)
    {
        $this->authorize('update', $referralSource);
        $data = $request->filtered();

        if ($referralSource->update($data)) {
            return new SuccessResponse('The referral source has been saved!', $referralSource);
        }

        return new ErrorResponse(500, 'Unable to save referral source.');
    }
}
