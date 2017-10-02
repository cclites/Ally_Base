<?php

namespace App\Http\Controllers\Business;

class ReportsController extends BaseController
{
    public function payments()
    {
        return view('business.reports.payments');
    }

    public function scheduled()
    {
        return view('business.reports.scheduled');
    }

    public function shifts()
    {
        return view('business.reports.shifts');
    }
}
