<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Http\Controllers\Controller;
use App\Billing\Payment;
use App\Reports\ShiftsReport;
use App\Shift;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Carbon;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $client = Client::with('payments.shifts.caregiver', 'payments')->find(auth()->id());
        return view('clients.payment_history', compact('client'));
    }

    public function show($id)
    {
        $payment_details = $this->getPaymentDetails($id);

        switch (auth()->user()->role_type) {
            case 'client':
                $print_url = '/payment-history/' . $id . '/print';
                break;
            default:
                $print_url = '/business/clients/payments/' . $id . '/print';
                break;
        }

        $payment_details['print_url'] = $print_url;
        return view('clients.payment_details', $payment_details);
    }

    public function printDetails($id)
    {
        $compactedDetails = $this->getPaymentDetails($id);
//        return view('clients.print.payment_details', $compactedDetails);

        $pdf = PDF::setOption('margin-left', '2mm')->setOrientation('landscape')->setOption('margin-right', '2mm')->loadView('clients.print.payment_details', $compactedDetails);//->setOrientation('landscape');
        return $pdf->download('payment_details.pdf');
    }

    protected function getPaymentDetails($payment_id)
    {
        $payment = Payment::with('business', 'client', 'shifts.activities')->find($payment_id);

        $report = new ShiftsReport();
        $report->query()
            ->where('payment_id', $payment_id)
            ->orderBy('checked_in_time');

        $shifts = $report->rows()->values()->map(function ($value) use ($payment) {
            $value = (object) $value;
            $value->activities = optional($payment->shifts->where('id', $value->id)->first())->activities;
            $value->checked_in_time = Carbon::parse($value->checked_in_time);
            $value->checked_out_time = Carbon::parse($value->checked_out_time);
            return $value;
        });

        $client = Client::find(auth()->id());
        $timezone = $client->business->timezone ?? 'America/New_York';

        return compact('payment', 'shifts', 'timezone');
    }
}
