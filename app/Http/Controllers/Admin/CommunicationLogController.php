<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\CommunicationLogResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CommunicationLog;

class CommunicationLogController extends Controller
{
    /**
     * Get the communication log report.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() && $request->filled('json')) {
            $log = CommunicationLog::forChannel($request->channel)
                ->whereSentBetween($request->start_date, $request->end_date)
                ->get();

            return CommunicationLogResource::collection($log);
        }

        return view_component('admin-communication-log', 'Communication Log');
    }
}
