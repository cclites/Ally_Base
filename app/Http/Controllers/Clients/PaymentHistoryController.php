<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Http\Controllers\Controller;
use App\Payment;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $client = Client::with('payments.method')->find(auth()->id());
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
