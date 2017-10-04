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
        $shifts = auth()->user()->role
            ->shifts()
            ->whereNotNull('checked_out_time')
            ->orderBy('checked_in_time', 'DESC')
            ->get();
        $shifts = $shifts->map(function($shift) {
            $shift->client_name = ($shift->client) ? $shift->client->name() : '';
            return $shift;
        });
        return view('caregivers.reports.shifts', compact('shifts'));
    }
}
