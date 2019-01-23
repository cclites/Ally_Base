<?php

namespace App\Payments;

use App\Gateway\ECSPayment;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;

class RefundProcessor
{
    /**
     * @var \App\Billing\GatewayTransaction
     */
    private $transaction;
    /**
     * @var \App\Gateway\ECSPayment
     */
    private $ECSPayment;

    public function __construct(GatewayTransaction $transaction, ECSPayment $ECSPayment = null)
    {
        $this->transaction = $transaction;
        $this->ECSPayment = $ECSPayment ?? new ECSPayment();
    }

    /**
     * Refund the transaction a certain amount
     *
     * @param $amount
     * @return \App\Billing\GatewayTransaction|false
     */
    public function refund($amount, $notes = '')
    {
       if ($amount > $this->transaction->amount) {
           throw new \Exception('The refund amount cannot be greater than the transaction amount');
       }

       if (!$method = $this->transaction->method) {
           throw new \Exception('No transaction method found.  Cannot refund.');
       }

       $payment = $this->transaction->payment;

       if ($transaction = $method->refund($this->transaction, $amount)) {
            $payment = Payment::create([
                'client_id' => optional($payment)->client_id,
                'business_id' => optional($payment)->business_id,
                'payment_type' => optional($payment)->payment_type,
                'amount' => $amount * -1,
                'transaction_id' => $transaction->id,
                'success' => true,
                'business_allotment' => 0,
                'caregiver_allotment' => 0,
                'system_allotment' => 0,
                'adjustment' => true,
                'notes' => $notes,
            ]);
            if (!$payment) {
                echo "Payment could not be recorded for refund.\n";
            }
       }

       return $transaction;
    }

}