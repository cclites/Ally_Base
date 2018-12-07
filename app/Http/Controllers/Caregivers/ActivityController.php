<?php

namespace App\Http\Controllers\Caregivers;

use App\Activity;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $activities = Activity::forRequestedBusinesses()->ordered()->get();

        return response()->json($activities);
    }
}
