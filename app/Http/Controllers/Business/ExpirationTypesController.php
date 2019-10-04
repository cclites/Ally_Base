<?php

namespace App\Http\Controllers\Business;

use App\ExpirationType;
use Illuminate\Http\Request;
use App\Responses\SuccessResponse;

class ExpirationTypesController extends BaseController
{
    /**
     * Get a list of the Chain's default expiration types.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = ExpirationType::where('chain_id', $this->businessChain()->id);

        return response()->json(
            $query->orderBy('type')
                ->get()
                ->values()
        );
    }

    /**
     * Store a new expiration type and return an updated list in the response.
     *
     * @param Request $request
     * @return SuccessResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required',
        ]);
        $this->businessChain()->expirationTypes()->create($data);

        $types = ExpirationType::where('chain_id', $this->businessChain()->id)->get();
        return new SuccessResponse('Added default expiration type', $types);
    }

    /**
     * Update an existing expiration type and return an updated list in the response.
     *
     * @param Request $request
     * @return SuccessResponse
     */
    public function update( Request $request, ExpirationType $expiration )
    {
        $data = $request->validate([
            'type' => 'required'
        ]);

        $expiration->update( $data );

        $types = ExpirationType::where('chain_id', $this->businessChain()->id)->get();
        return new SuccessResponse('Edited default expiration type', $types);
    }

    /**
     * Destroy a default type and return an updated list in the response
     *
     * @param ExpirationType $expiration
     * @return SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(ExpirationType $expiration)
    {
        $this->authorize('delete', $expiration);

        $expiration->delete();

        return new SuccessResponse('Removed default expiration type');
    }
}