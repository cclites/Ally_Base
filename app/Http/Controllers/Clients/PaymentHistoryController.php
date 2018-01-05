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
        return view('clients.payment_history', compact('client'));
    }

    public function show($id)
    {
        $payment_details = $this->getPaymentDetails($id);

        switch (auth()->user()->role_type) {
            case 'office_user':
                $print_url = '/business/clients/payments/' . $id . '/print';
                break;
            case 'client':
                $print_url = '/payment-history/' . $id . '/print';
                break;
        }

        $payment_details['print_url'] = $print_url;
        return view('clients.payment_details', $payment_details);
    }

    public function printDetails($id)
    {
        $payment_details = $this->getPaymentDetails($id);

        //return view('clients.print.payment_details', $payment_details);

        $pdf = PDF::loadView('clients.print.payment_details', $payment_details)->setOrientation('landscape');
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
            $value->activities = optional($payment->shifts->where('id', $value->id)->first())
                ->activities
                ->pluck('name')
                ->unique()
                ->sortBy('name')
                ->values();
            $value->checked_in_time = Carbon::parse($value->checked_in_time);
            $value->checked_out_time = Carbon::parse($value->checked_out_time);
            return $value;
        });

        return compact('payment', 'shifts');
    }
}
