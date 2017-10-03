<?php

namespace App\Http\Controllers\Caregivers;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function deposits()
    {
        return view('caregivers.reports.deposits');
    }

    public function shifts()
    {
        return view('caregivers.reports.shifts');
    }
}
