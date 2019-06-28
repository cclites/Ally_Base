<?php

namespace App\Http\Controllers\Business;

use App\ExpirationType;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;
use App\Responses\SuccessResponse;


class ExpirationTypesController extends BaseController
{
    /**
     * Get a list of the Chain's default expiration types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(
            $this->businessChain()->expirationTypes()->get()
        );
    }

    /**
     * Store a new expiration type and return an updated list in the response
     * @param Request $request
     * @return SuccessResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate(['type' => 'required|max:255']);

        if ($expirationType = $this->businessChain()->expirationTypes()->create($data)) {
            return new SuccessResponse('Added default expiration type', $expirationType);
        }

        return new ErrorResponse(500, 'An error occurred while trying to create a new expiration type.  Please try again.');
    }

    /**
     * Destroy a default type and return an updated list in the response
     *
     * @param ExpirationType $expirationType
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(ExpirationType $expirationType)
    {
        $this->authorize('delete', $expirationType);

        $expirationType->delete();

        return new SuccessResponse('Removed default expiration type');
    }
}
