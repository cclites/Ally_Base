<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Reports\ShiftsReport;
use Carbon\Carbon;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $client = Client::with('payments.method', 'payments.shifts.caregiver', 'payments')->find(auth()->id());
        $client->payments = $client->payments->map(function ($payment) {
            if ($payment->shifts()->exists()) {
                $checked_in_time = $payment->shifts->first()->checked_in_time;
                $payment->week = [
                    'start' => $checked_in_time->setIsoDate($checked_in_time->year, $checked_in_time->weekOfYear)->toDateString(),
                    'end' => $checked_in_time->setIsoDate($checked_in_time->year, $checked_in_time->weekOfYear,7)->toDateString()
                ];
            }
            return $payment;
        });
        return view('clients.payment_history', compact('client'));
    }

    public function show($id)
    {
        $report = new ShiftsReport();
        $report->query()
            ->with('activities')
            ->where('payment_id', $id)
            ->orderBy('checked_in_time');

        $payment = json_encode([
            'id' => $id,
            'shifts' => $report->rows()->toArray()
        ]);
        return view('clients.payment_details', compact('payment'));
    }

    public function printDetails($id)
    {
        $payment = Payment::with('business', 'client')->find($id)->toArray();

        $report = new ShiftsReport();
        $report->query()
            ->with('activities')
            ->where('payment_id', $id)
            ->orderBy('checked_in_time');

        $payment['shifts'] = $report->rows()->values();
        return view('clients.print.payment_details', compact('payment'));
    }
}
