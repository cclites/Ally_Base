<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\Resources\ClaimRemitResource;
use App\Http\Controllers\Business\BaseController;
use App\Claims\Requests\CreateClaimRemitRequest;
use App\Claims\Requests\GetClaimRemitsRequest;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use App\Claims\ClaimRemit;

class ClaimRemitController extends BaseController
{
    /**
     * Get a list of ClaimRemits.
     *
     * @param GetClaimRemitsRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(GetClaimRemitsRequest $request)
    {
        if ($request->forJson()) {
            $filters = $request->filtered();

            $results = ClaimRemit::forRequestedBusinesses()
                ->forDateRange($filters['start_date'], $filters['end_date'])
                ->forPayer($filters['payer_id'])
                ->withReferenceId($filters['reference'])
                ->withType($filters['type'])
                ->withStatus($filters['status'])
                ->get();

            return ClaimRemitResource::collection($results);
        }

        return view_component(
            'claim-remits',
            'Claim Remits'
        );
    }

    /**
     * Create a new ClaimRemit.
     *
     * @param CreateClaimRemitRequest $request
     * @return ErrorResponse|SuccessResponse
     */
    public function store(CreateClaimRemitRequest $request)
    {
        if ($remit = ClaimRemit::create($request->filtered())) {
            return new SuccessResponse('Remit has been created.', $remit);
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create a Remit.  Please try again.');
    }
}
