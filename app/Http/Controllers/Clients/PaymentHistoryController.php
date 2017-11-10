<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Http\Controllers\Controller;
use App\Payment;
use Carbon\Carbon;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $client = Client::with('payments.method', 'payments.shifts')->find(auth()->id());

        $client->payments = $client->payments->map(function ($payment) {
            $week_start_date = Carbon::now();
            $week_end_date = Carbon::now();
            $checked_in_time = $payment->shifts->first()->checked_in_time;
            $payment->week = [
                'start' => $checked_in_time->setIsoDate($checked_in_time->year, $checked_in_time->weekOfYear)->toDateString(),
                'end' => $checked_in_time->setIsoDate($checked_in_time->year, $checked_in_time->weekOfYear, 7)->toDateString()
            ];
            return $payment;
        });
        return view('clients.payment_history', compact('client'));
    }

    public function show($id)
    {
        $payment = Payment::with('shifts.client')->find($id);
        return view('clients.payment_details', compact('payment'));
    }

    public function printDetails($id)
    {
        $payment = Payment::with('shifts.caregiver', 'shifts.activities', 'business', 'client')->find($id);
        return view('clients.print.payment_details', compact('payment'));
    }
}
