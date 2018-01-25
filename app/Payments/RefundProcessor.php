<?php

namespace App\Payments;

use App\Gateway\ECSPayment;
use App\GatewayTransaction;
use App\Payment;

class RefundProcessor
{
    /**
     * @var \App\GatewayTransaction
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
     * @return \App\GatewayTransaction|false
     */
    public function refund($amount)
    {
       if ($amount > $this->transaction->amount) {
           throw new \Exception('The refund amount cannot be greater than the transaction amount');
       }

       $payment = $this->transaction->payment;

       if ($transaction = $this->ECSPayment->refund($this->transaction, $amount)) {
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
            ]);
            if (!$payment) {
                echo "Payment could not be recorded for refund.\n";
            }
       }

       return $transaction;
    }

}