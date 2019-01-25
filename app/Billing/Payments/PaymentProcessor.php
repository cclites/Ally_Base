<?php
namespace App\Billing\Payments;

use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\TransactionRefund;
use App\Client;
use App\Billing\Exceptions\PaymentMethodDeclined;
use App\Billing\Exceptions\PaymentMethodError;

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
     * @param \App\Client $client
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @param float $amount
     * @param string $currency
     * @return \App\Billing\Payment|null
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function charge(Client $client, PaymentMethodStrategy $strategy, float $amount, string $currency = 'USD'): Payment
    {
        if ($transaction = $strategy->charge($amount, $currency)) {
            if (!$transaction->success) {
                throw new PaymentMethodDeclined();
            }

            return Payment::create([
                'client_id' => $client->id,
                'business_id' => $client->business_id,
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
                'client_id' => $payment->client_id,
                'business_id' => $payment->business_id,
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