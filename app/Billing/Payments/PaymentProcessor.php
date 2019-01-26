<?php
namespace App\Billing\Payments;

use App\Billing\Exceptions\PayerAssignmentError;
use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Exceptions\PaymentMethodDeclined;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\TransactionRefund;

class PaymentProcessor
{
    /**
     * @var \App\Gateway\CreditCardPaymentInterface
     */
    protected $ccGateway;

    /**
     * @var \App\Gateway\ACHPaymentInterface
     */
    protected $achGateway;

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
            $strategy = $payer->getPaymentMethod()->getDefaultStrategy();
        }

        if ($transaction = $strategy->charge($amount, $currency)) {
            if (!$transaction->success) {
                throw new PaymentMethodDeclined();
            }

            return Payment::create([
                'payer_id' => $payer,
                'client_id' => $client->id ?? null,
                'amount' => $transaction->amount,
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);
        }

        throw new PaymentMethodError();
    }

    /**
     * Refund a previously charged transaction
     *
     * @param \App\Billing\Payment $payment
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @param float $amount
     * @param string|null $notes
     * @return \App\Billing\TransactionRefund|null
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    public function refund(Payment $payment, PaymentMethodStrategy $strategy, float $amount, string $notes = null): ?TransactionRefund
    {
        if ($amount <= 0)  {
            throw new PaymentAmountError("The refund amount cannot be less than $0");
        }

        if (!$payment->transaction) {
            throw new PaymentMethodError('Unable to locate transaction for Payment ID ' . $payment->id);
        }

        if (!$payment->transaction->method) {
            throw new PaymentMethodError('Missing payment method for Payment ID ' . $payment->id);
        }

        if ($transaction = $strategy->refund($payment->transaction, $amount)) {
            if (!$transaction->success) {
                throw new PaymentMethodError("The refund transaction was unsuccessful.");
            }

            $refundPayment = Payment::create([
                'payment_type' => $payment->payment_type,
                'amount' => $amount * -1,
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
                'adjustment' => true,
                'notes' => $notes,
            ]);

            return TransactionRefund::create([
                'amount' => $amount,
                'refunded_transaction_id' => $payment->transaction->id,
                'refunded_payment_id' => $payment->id,
                'issued_transaction_id' => $transaction->id,
                'issued_payment_id' => $refundPayment->id,
            ]);
        }

        throw new PaymentMethodError("Unknown gateway error when attempting refund.");
    }
}