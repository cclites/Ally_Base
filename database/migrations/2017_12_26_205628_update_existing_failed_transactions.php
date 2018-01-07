<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateExistingFailedTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APP_ENV') !== 'production') {
            return;
        }

        $transactions = \App\GatewayTransaction::whereHas('history', function($q) {
            $q->where('status', 'failed');
        })->get();

        foreach($transactions as $transaction) {
            $transaction->update(['success' => 0]);
            if ($payment = $transaction->payment) {
                $payment->update(['success' => 0]);
            }
            if ($deposit = $transaction->deposit) {
                $deposit->update(['success' => 0]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
