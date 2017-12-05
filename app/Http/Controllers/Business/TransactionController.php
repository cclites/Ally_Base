<?php

namespace App\Http\Controllers\Business;

use App\GatewayTransaction;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{
    public function show(GatewayTransaction $transaction)
    {
        $transaction->load(['payment', 'deposit', 'history']);
        if (!$this->hasAccessTo($transaction)) {
            return new ErrorResponse(403, 'You do not have access to this transaction.');
        }

        return view('business.transactions.show', compact('transaction'));
    }

    protected function hasAccessTo(GatewayTransaction $transaction)
    {
        if ($transaction->payment) {
            return ($transaction->payment->business_id == $this->business()->id);
        }

        if ($transaction->deposit) {
            return ($transaction->deposit->business_id == $this->business()->id);
        }

        return false;
    }
}
