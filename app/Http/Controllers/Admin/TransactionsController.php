<?php


namespace App\Http\Controllers\Admin;


use App\Billing\GatewayTransaction;
use App\Http\Controllers\Controller;
use App\Payments\RefundProcessor;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Billing\TransactionRefund;
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
        $transaction->load(['payment', 'deposit', 'history', 'refunds', 'refunds.issuedPayment']);

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

    public function refund(GatewayTransaction $transaction, Request $request)
    {
        $request->validate([
            'amount' => 'numeric|min:0.01',
            'notes' => 'required|string',
        ]);

        $data = [
            'amount' => $request->amount,
            'refunded_transaction_id' => $transaction->id,
            'refunded_payment_id' => $transaction->payment->id,
        ];

        if ($request->amount > $transaction->amount) return new ErrorResponse(400, 'The refund amount cannot be greater than the transaction amount.');

        try {
            $refund = new RefundProcessor($transaction);
            $issuedTransaction = $refund->refund($request->amount, $request->notes);
            $data += [
                'issued_transaction_id' => $issuedTransaction->id,
                'issued_payment_id' => $issuedTransaction->payment->id,
            ];
            $refund = TransactionRefund::create($data);
            return new CreatedResponse('Successful refund of $' . $request->amount, $refund);
        }
        catch (\Exception $e) {
            $message = "Error recording or processing refund\n\n" . print_r($data, true) . "\n\n" . $e->getMessage();
            \Log::error($message);
            \Mail::raw($message, function($m) {
                $m->subject('AllyMS Refund Error');
                $m->to(['devon@jtrsolutions.com', 'jason@jtrsolutions.com']);
            });
        }
        return new ErrorResponse(500, 'There was an error handling the refund.  An email has been sent, do NOT resubmit.');
    }



}
