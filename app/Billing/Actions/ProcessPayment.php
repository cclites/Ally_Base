<?php

namespace App\Billing\Actions;

use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Exceptions\PaymentMethodDeclined;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Business;
use App\User;

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
     * @throws PaymentAmountError
     */
    function charge(PaymentMethodStrategy $strategy, float $amount, string $currency = 'USD'): Payment
    {
        if ($amount <= 0) {
            throw new PaymentAmountError("The payment amount cannot be less than $0");
        }

        if ($transaction = $strategy->charge($amount, $currency)) {
            if (!$transaction->success) {
                throw new PaymentMethodDeclined();
            }

            // Get payment method owner
            if ($owner = $strategy->getPaymentMethod()->getOwnerModel()) {
                if ($owner instanceof User) {
                    $client = $owner->client;
                }
                if ($owner instanceof Business) {
                    $business = $owner;
                }
            }

            $payment = new Payment([
                'client_id' => $client->id ?? null,
                'business_id' => $business->id ?? $client->business_id ?? null,
                'amount' => $transaction->amount,
                'payment_type' => $strategy->getPaymentMethod()->getPaymentType(),
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
     * Get a CALCULATED ESTIMATE of what the Ally fee was that was charged
     * based on the payment method used.  This becomes the stored value
     * for system allotment, but could potentially be different than
     * the numbers used to generate the invoice.
     *
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @param float $amount
     * @return float|null
     */
    function getAllyFee(PaymentMethodStrategy $strategy, float $amount)
    {
        if ($this->allyFee !== null) {
            return $this->allyFee;
        }

        $allyFee = $strategy->getPaymentMethod()->getAllyFee($amount, true);
        return $allyFee;
    }
}
