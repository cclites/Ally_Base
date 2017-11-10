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
        // todo fix this workaround method for getting caregiver payments when a better relationship is created
        $caregiver = Caregiver::with('shifts.payment')->find(auth()->id());
        $payments = $caregiver->shifts->map(function ($shift) {
            $payment = null;
            if (isset($shift->payment)) {
                $payment = $shift->payment;
                $payment->week = [
                    'start' => $shift->checked_in_time->setIsoDate($shift->checked_in_time->year, $shift->checked_in_time->weekOfYear)->toDateString(),
                    'end' => $shift->checked_in_time->setIsoDate($shift->checked_in_time->year, $shift->checked_in_time->weekOfYear, 7)->toDateString()
                ];
            }
            return $payment;
        })
            ->filter();

        if ($payments->count()) {
            $payments = $payments->unique();
        }
        return view('caregivers.reports.payment_history', compact('caregiver', 'payments'));
    }

    public function paymentDetails($id)
    {
        $payment = Payment::with('shifts.client')->find($id);
        return view('caregivers.reports.payment_details', compact('payment'));
    }
}
