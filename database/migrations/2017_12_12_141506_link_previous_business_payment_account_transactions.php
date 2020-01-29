<?php

use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use Illuminate\Database\Migrations\Migration;

class LinkPreviousBusinessPaymentAccountTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // One time migration for production only
        if (env('APP_ENV') !== 'production') return;

        $businesses = \App\Business::has('paymentAccount')->orderBy('id')->get();
        $transactions = GatewayTransaction::where('transaction_type', 'sale')->where('success', 1)->orderBy('id')->get();

        foreach($transactions as $transaction) {
            if ($details = $this->getTransactionDetails($transaction)) {
                foreach($businesses as $business) {
                    if (
                        substr($business->paymentAccount->account_number, -4) === substr($details['check_account'], -4)
                        && $business->paymentAccount->routing_number == $details['check_aba']
                    )
                    {
                        // Matched routing number and last four digits of account
                        if ($payment = $transaction->payment) {
                            $payment->client_id = null;
                            $payment->business_id = $business->id;
                            printf("Reassigning payment %s from client to business %s (Transaction %s)\n", $payment->id, $business->name, $transaction->transaction_id);
                            $payment->save();
                        }
                        else {
                            printf("Missing payment of %s found for business %s (Transaction %s)\n", $transaction->amount, $business->name, $transaction->transaction_id);
                            $payment = Payment::where('business_id', $business->id)->where('amount', $transaction->amount)->whereNull('transaction_id')->first();
                            if ($payment) {
                                $payment->transaction_id = $transaction->id;
                                $payment->client_id = null;
                                $payment->save();
                            }
                            else {
                                $payment = Payment::create([
                                    'business_id' => $business->id,
                                    'payment_type' => 'ACH',
                                    'amount' => $transaction->amount,
                                    'transaction_id' => $transaction->id,
                                    'success' => $transaction->success,
                                    'created_at' => $transaction->created_at,
                                ]);
                            }
                            break;
                        }
                        break;
                    }
                }
                printf("No match for transaction %s (%s)\n", $transaction->transaction_id, $transaction->id);
            }
        }


    }

    protected function getTransactionDetails(GatewayTransaction $transaction)
    {
        $cacheKey = 'tmp_transaction_' . $transaction->id;
        if (!Cache::has($cacheKey)) {
            usleep(50000);
            $query = new \App\Billing\Gateway\ECSQuery();
            $result = $query->find($transaction->transaction_id);
            if (!$result) {
                return false;
            }

            $details = json_decode(json_encode($result->transaction), true);
            Cache::put($cacheKey, $details, 60 * 60);
        }

        $details = Cache::get($cacheKey);
        if ($details['transaction_type'] == 'ck') {
            return $details;
        }

        return false;

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
