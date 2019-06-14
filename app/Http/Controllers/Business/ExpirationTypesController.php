<?php

namespace App\Http\Controllers\Business;

use App\ExpirationType;

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
            ExpirationType::whereNull('chain_id')
            ->orWhere('chain_id', $this->businessChain()->id)
            ->orderBy('type')
            ->get()
            ->values()
        );
    }
}
