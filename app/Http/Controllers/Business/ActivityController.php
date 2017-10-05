<?php

namespace App\Http\Controllers\Business;

class ActivityController extends BaseController
{
    public function index()
    {
        $activities = $this->business()->activities->sortBy('code');
        return view('business.activities.index', compact('activities'));
    }

}
