<?php

namespace App\Http\Controllers\Api\Quickbooks;

use App\Http\Requests\Api\Quickbooks\QuickbooksDesktopApiRequest;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use Carbon\Carbon;

class QuickbooksDesktopController extends Controller
{
    /**
     * Pulse the API and make sure connection is working.
     *
     * @param QuickbooksDesktopApiRequest $request
     * @return QuickbooksApiResponse
     */
    public function ping(QuickbooksDesktopApiRequest $request)
    {
        $request->connection()->update([
            'last_connected_at' => Carbon::now(),
        ]);

        return new QuickbooksApiResponse('pong');
    }
}