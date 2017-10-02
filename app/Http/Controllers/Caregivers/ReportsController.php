<?php

namespace App\Http\Controllers\Caregivers;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function payments()
    {
        return view('caregivers.reports.payments');
    }

    public function scheduled()
    {
        return view('caregivers.reports.scheduled');
    }

    public function shifts()
    {
        return view('caregivers.reports.shifts');
    }
}
