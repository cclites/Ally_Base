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
     * @param \App\Billing\Payer $payer
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @param float $amount
     * @param string $currency
     * @return \App\Billing\Payment|null
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function charge(Payer $payer, ?PaymentMethodStrategy $strategy, float $amount, string $currency = 'USD'): Payment
    {
        if ($amount <= 0)  {
            throw new PaymentAmountError("The payment amount cannot be less than $0");
        }

        if ($payer->isPrivatePay()) {
            $client = $payer->getPrivatePayer();
            if (!$client) {
                throw new PayerAssignmentError("The private payer does not have a client record attached.");
            }
        }

        if (!$strategy) {
            $strategy = $payer->getPaymentMethod()->getPaymentStrategy();
        }

        if ($transaction = $strategy->charge($amount, $currency)) {
            if (!$transaction->success) {
                throw new PaymentMethodDeclined();
            }

            return Payment::create([
                'payer_id' => $payer,
                'client_id' => $client->id ?? null,
                'amount' => $transaction->amount,
                'system_allotment' => $this->getAllyFee($strategy, $amount),
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);
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