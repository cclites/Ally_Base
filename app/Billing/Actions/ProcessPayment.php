<?php
namespace App\Billing\Actions;

use App\Billing\Exceptions\PayerAssignmentError;
use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Exceptions\PaymentMethodDeclined;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;


class ProcessPayment
{
    /**
     * @var null|float
     */
    protected $allyFee = null;

    /**
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @param float $amount
     * @param string $currency
     * @return \App\Billing\Payment|null
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function charge(PaymentMethodStrategy $strategy, float $amount, string $currency = 'USD'): Payment
    {
        if ($amount <= 0)  {
            throw new PaymentAmountError("The payment amount cannot be less than $0");
        }

        if ($transaction = $strategy->charge($amount, $currency)) {
            if (!$transaction->success) {
                throw new PaymentMethodDeclined();
            }

            $payment = new Payment([
                'client_id' => $client->id ?? null,
                'amount' => $transaction->amount,
                'payment_type' => $strategy->getPaymentType(),
                'system_allotment' => $this->getAllyFee($strategy, $amount),
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);

            $payment->setPaymentMethod($strategy->getPaymentMethod());
            $payment->save();

            return $payment;
        }

        throw new PaymentMethodError();
    }

    /**
     * Set a predetermined Ally Fee instead of calculating it from the payment amount and payment method
     *
     * @param float|null $fixedFee
     */
    function setAllyFee(?float $fixedFee)
    {
        $this->allyFee = $fixedFee;
    }

    /**
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @param float $amount
     * @return float|null
     */
    function getAllyFee(PaymentMethodStrategy $strategy, float $amount)
    {
        if ($this->allyFee !== null) {
            return $this->allyFee;
        }

        $percentage = $strategy->getPaymentMethod()->getAllyPercentage();
        $allyFee = subtract($amount, divide($amount, add(1, $percentage)));
        return $allyFee;
    }
}