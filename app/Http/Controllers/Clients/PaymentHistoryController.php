<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Reports\ShiftsReport;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Carbon;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $client = Client::with('payments.shifts.caregiver', 'payments')->find(auth()->id());
        $client->payments = $client->payments->orderBy('created_at', 'DESC')->map(function ($payment) {
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

        $payment = (object) [
            'id' => $id,
            'shifts' => $report->rows()
        ];

        switch (auth()->user()->role_type) {
            case 'office_user':
                $print_url = '/business/clients/payments/' . $payment->id . '/print';
                break;
            case 'client':
                $print_url = '/payment-history/' . $payment->id . '/print';
                break;
        }

        return view('clients.payment_details', compact('payment', 'print_url'));
    }

    public function printDetails($id)
    {
        $payment = Payment::with('business', 'client.evvAddress')->find($id);

        $report = new ShiftsReport();
        $report->query()
//            ->with('activities')  // This does not work
            ->where('payment_id', $id)
            ->orderBy('checked_in_time');

        $payment->shifts = $report->rows()->values()->map(function ($value) {
            $value = (object) $value;
            $value->checked_in_time = Carbon::parse($value->checked_in_time);
            $value->checked_out_time = Carbon::parse($value->checked_out_time);
            return $value;
        });

        if (request('view')) {
            return view('clients.print.payment_details', compact('payment'));
        }
        
        $pdf = PDF::loadView('clients.print.payment_details', compact('payment'))->setOrientation('landscape');
        return $pdf->download('payment_details.pdf');
    }
}
