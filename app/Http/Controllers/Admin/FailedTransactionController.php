<?php

namespace App\Http\Controllers\Admin;

use App\GatewayTransaction;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FailedTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $query = GatewayTransaction::with([
                'deposit',
                'payment',
                'deposit.caregiver',
                'deposit.caregiver.businesses',
                'deposit.business',
                'payment.client',
                'payment.client.business',
                'payment.business',
                'lastHistory'
            ]);
            return $query->has('failedTransaction')
                         ->where('success', 1) // Do not include already failed transactions
                         ->orderBy('created_at')
                         ->get();
        }
        return view('admin.reports.failed_transactions');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\GatewayTransaction $failedTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GatewayTransaction $failedTransaction)
    {
        $data = $request->validate(['failed' => 'required|boolean']);
        if ($data['failed']) {
            if ($failedTransaction->recordFailure()) {
                return new SuccessResponse('The transaction has been recorded as a true failure.');
            }
            return new ErrorResponse(500, 'Unable to mark the transaction as a true failure.');
        }
        return $this->destroy($failedTransaction);
    }

    /**
     * Disregard the failed transaction, and clean up any holds.
     *
     * @param  \App\GatewayTransaction $failedTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(GatewayTransaction $failedTransaction)
    {
        if ($failedTransaction->discardFailure()) {
            $failedTransaction->removeHolds();

            return new SuccessResponse('Acknowledged a successful transaction.');
        }
        return new ErrorResponse(500, 'Unable to process failed transaction.');
    }
}
