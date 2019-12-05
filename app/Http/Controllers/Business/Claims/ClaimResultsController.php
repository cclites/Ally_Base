<?php

namespace App\Http\Controllers\Business\Claims;

use App\Claims\ClaimInvoice;
use App\Http\Controllers\Business\BaseController;

class ClaimResultsController extends BaseController
{
    /**
     * Get the response results from an HHA transmission.
     *
     * // TODO: implement Tellus results
     *
     * @param ClaimInvoice $claim
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ClaimInvoice $claim)
    {
        $hhaFile = $claim->hhaFiles()
            ->with('results')
            ->latest()
            ->first();

        if (empty($hhaFile)) {
            return response()->json([]);
        }

        return response()->json($hhaFile->results);
    }
}