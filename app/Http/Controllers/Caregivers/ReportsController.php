<?php

namespace App\Http\Controllers\Caregivers;

use App\Caregiver;
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
        $caregiver = Caregiver::with('shifts.payment')->find(auth()->id());
        $payments = $caregiver->shifts->map(function ($shift) {
            $payment = $shift->payment;
            if ($payment) {
                $payment->week = [
                    'start' => $shift->checked_in_time->setIsoDate($shift->checked_in_time->year, $shift->checked_in_time->weekOfYear)->toDateString(),
                    'end' => $shift->checked_in_time->setIsoDate($shift->checked_in_time->year, $shift->checked_in_time->weekOfYear, 7)->toDateString()
                ];
            }
            return $payment;
        })->unique();
        return view('caregivers.reports.payment_history', compact('caregiver', 'payments'));
    }

    public function paymentDetails($id)
    {
        $payment = Payment::with('shifts.client')->find($id);
        return view('caregivers.reports.payment_details', compact('payment'));
    }
}
