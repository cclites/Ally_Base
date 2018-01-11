<?php

namespace App\Http\Controllers\Caregivers;

use App\Caregiver;
use App\Deposit;
use App\Http\Controllers\Controller;
use App\Reports\ShiftsReport;
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
        $deposits = Deposit::with('shifts')->where('caregiver_id', $caregiver->id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function ($deposit) {
                $deposit->amount = floatval($deposit->amount);
                $deposit->start = Carbon::instance($deposit->created_at)->subWeek()->startOfWeek()->toDateString();
                $deposit->end = Carbon::instance($deposit->created_at)->subWeek()->endOfWeek()->toDateString();
                return $deposit;
            });

        return view('caregivers.reports.payment_history', compact('caregiver', 'deposits'));
    }

    public function paymentDetails($id)
    {
        $report = new ShiftsReport();
        $report->query()->with('deposits')
            ->whereHas('deposits', function ($query) use ($id) {
                $query->where('deposits.id', $id);
            })
            ->where('caregiver_id', auth()->id());
        $shifts = $report->rows();

        return view('caregivers.reports.payment_details', compact('shifts'));
    }
}
