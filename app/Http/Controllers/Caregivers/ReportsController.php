<?php

namespace App\Http\Controllers\Caregivers;

use App\Caregiver;
use App\Deposit;
use App\Http\Controllers\Controller;
use App\Payment;
use Carbon\Carbon;

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
            ->with('activities')
            ->whereNotNull('checked_out_time')
            ->orderBy('checked_in_time', 'DESC')
            ->get();
        $shifts = $shifts->map(function($shift) {
            $shift->client_name = ($shift->client) ? $shift->client->name() : '';
            $shift->activity_names = collect($shift->activities)
                ->sortBy('name')
                ->pluck('name')
                ->implode(', ');
            return $shift;
        });

        return view('caregivers.reports.shifts', compact('shifts'));
    }

    public function paymentHistory()
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $caregiver = Caregiver::find(auth()->id());

        $deposits = Deposit::where('caregiver_id', $caregiver->id)
            ->get()
            ->map(function ($deposit) {
                $deposit->start = Carbon::instance($deposit->created_at)->subWeek()->startOfWeek()->toDateString();
                $deposit->end = Carbon::instance($deposit->created_at)->subWeek()->endOfWeek()->toDateString();
                return $deposit;
            });

        return view('caregivers.reports.payment_history', compact('caregiver', 'deposits'));
    }

    public function paymentDetails($id)
    {
        $deposit = Deposit::with(['shifts.client', 'shifts' => function ($query) {
            $query->orderBy('checked_in_time');
        }])->find($id);
        return view('caregivers.reports.payment_details', compact('deposit'));
    }
}
