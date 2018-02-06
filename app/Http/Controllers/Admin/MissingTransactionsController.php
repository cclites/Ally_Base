<?php

namespace App\Http\Controllers\Admin;

use App\GatewayTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MissingTransactionsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $transactions = GatewayTransaction::whereDoesntHave('payment')
                                              ->whereDoesntHave('deposit')
                                              ->with('method')
                                              ->with('lastHistory')
                                              ->where('id', '>', 12) // Disregard test transactions
                                              ->whereNotIn('transaction_type', ['validate'])
                                              ->orderBy('created_at')
                                              ->get();
            return $transactions->map(function(GatewayTransaction $transaction) {
                $transaction->owner = null;
                if ($transaction->method) {
                    if ($transaction->method->user) {
                        $transaction->owner = $transaction->method->user;
                    }
                    if ($transaction->method->business) {
                        $transaction->owner = $transaction->method->business;
                    }
                }
                return $transaction;
            });

            return $transactions;
        }

        return view('admin.transactions.missing');
    }
}
