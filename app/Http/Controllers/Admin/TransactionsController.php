<?php


namespace App\Http\Controllers\Admin;


use App\GatewayTransaction;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function index()
    {
        return view('admin.transactions.index');
    }

    public function report(Request $request)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        // Make UTC to match DB
        $startDate->setTimezone('UTC');
        $endDate->setTimezone('UTC');

        $transactions = GatewayTransaction::with([])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->get();
        return $transactions;
    }

    public function show(GatewayTransaction $transaction)
    {
        $transaction->load(['payment', 'deposit', 'history']);

        $user = null;
        $userType = null;
        if ($payment = $transaction->payment) {
            $payment->load(['client', 'business']);
            if ($payment->client) {
                $userType = 'client';
                $user = $payment->client;
            }
            elseif ($payment->business) {
                $userType = 'business';
                $user = $payment->business;
            }
        }
        if ($deposit = $transaction->deposit) {
            if ($deposit->caregiver) {
                $userType = 'caregiver';
                $user = $deposit->caregiver;
            }
            elseif ($deposit->business) {
                $userType = 'business';
                $user = $deposit->business;
            }
        }

        return view('admin.transactions.show', compact('transaction', 'user', 'userType'));
    }

}
