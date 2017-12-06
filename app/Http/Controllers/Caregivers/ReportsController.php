<?php

namespace App\Http\Controllers\Caregivers;

use App\Caregiver;
use App\Deposit;
use App\Http\Controllers\Controller;
use App\Payment;

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

    public function paymentHistory()
    {
        $caregiver = Caregiver::find(auth()->id());

        $deposits = Deposit::with(['shifts.client', 'shifts' => function ($query) {
            $query->orderBy('checked_in_time');
        }])
            ->where('caregiver_id', $caregiver->id)
            ->get()
            ->map(function ($deposit) {
                $deposit->start = $deposit->shifts->first()->checked_in_time->toDateString();
                $deposit->end = $deposit->shifts->last()->checked_in_time->toDateString();
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
