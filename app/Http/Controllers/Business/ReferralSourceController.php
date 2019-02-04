<?php
namespace App\Http\Controllers\Business;

use App\Http\Requests\UpdateReferralSourceRequest;
use App\ReferralSource;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ReferralSourceController extends BaseController
{
    public function index(Request $request)
    {
        $referralsources = $this->businessChain()
            ->referralSources()
            ->forType($request->type)
            ->ordered()
            ->get();

        if (request()->expectsJson()) {
            return response()->json($referralsources);
        }

        return view('business.referral.list', compact('referralsources', 'edit', 'create', 'type'));
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

        return view('business.referral.show', compact('referralSource'));
    }

    public function store(UpdateReferralSourceRequest $request)
    {
        $data = $request->validated();
        $this->authorize('create', [ReferralSource::class, $data]);

        if ($referralSource = $this->businessChain()->referralSources()->create($data)) {
        return new CreatedResponse('The referral source has been created!', $referralSource);
        }

        return new ErrorResponse(500, 'Unable to create referral source.');
    }

    public function update(ReferralSource $referralSource, UpdateReferralSourceRequest $request)
    {
        $this->authorize('update', $referralSource);
        $data = $request->validated();

        if ($referralSource->update($data)) {
            return new SuccessResponse('The referral source has been saved!', $referralSource);
        }

        return new ErrorResponse(500, 'Unable to save referral source.');
    }
}
