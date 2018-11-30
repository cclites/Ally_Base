<?php

namespace App\Http\Controllers\Business;

use App\GatewayTransaction;
use App\Responses\ErrorResponse;

class TransactionController extends BaseController
{
    public function show(GatewayTransaction $transaction)
    {
        $transaction->load(['payment', 'deposit', 'history']);
        $this->authorize('read', $transaction);

        return view('business.transactions.show', compact('transaction'));
    }
}
