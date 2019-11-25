<?php

namespace App\Billing\Actions;

use App\Billing\ClientInvoice;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Queries\PaymentQuery;

class ProcessInvoicePayment
{
    /**
     * @var \App\Billing\Actions\ProcessPayment
     */
    protected $paymentProcessor;
    /**
     * @var \App\Billing\Actions\ApplyPayment
     */
    protected $paymentApplicator;
    /**
     * @var \App\Billing\Queries\PaymentQuery
     */
    protected $paymentQuery;

    public function __construct(ProcessPayment $paymentProcessor, ApplyPayment $paymentApplicator, PaymentQuery $paymentQuery)
    {
        $this->paymentProcessor = $paymentProcessor;
        $this->paymentApplicator = $paymentApplicator;
        $this->paymentQuery = $paymentQuery;
    }

    /**
     * @param \App\Billing\ClientInvoice $invoice
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @param float|null $amount
     * @return \App\Billing\Payment
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     * @throws \App\Billing\Exceptions\PaymentAmountError
     */
    function payInvoice(ClientInvoice $invoice, PaymentMethodStrategy $strategy, ?float $amount = null): Payment
    {
        if ($amount === null) {
            $amount = $invoice->getAmountDue();
        }

        $payment = $this->paymentProcessor->charge($strategy, $amount);
        if (!$payment) {
            throw new PaymentMethodError("Unable to receive payment for invoice.");
        }

        $this->paymentApplicator->apply($invoice, $payment, $amount);
        return $payment;
    }

    /**
     * @param ClientInvoice[] $invoices
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @return \App\Billing\Payment
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     * @throws \App\Billing\Exceptions\PaymentAmountError
     */
    function payInvoices(iterable $invoices, PaymentMethodStrategy $strategy): Payment
    {
        $amount = $this->sumInvoiceAmounts($invoices);

//        $existingPayments = $this->paymentQuery->forPayer($payer)->hasAmountAvailable()->get();
//        if ($existingPayments->count()) {
//            foreach($existingPayments as $payment) {
//                $this->applyPayment($invoices, $payment);
//                $amount = $this->sumInvoiceAmounts($invoices); // recalculate after allocating existing payments
//            }
//        }

        $payment = $this->paymentProcessor->charge($strategy, $amount);
        if (!$payment) {
            throw new PaymentMethodError("Unable to receive payment for invoices.");
        }

        $this->applyPayment($invoices, $payment);

        return $payment;
    }

    /**
     * @param ClientInvoice[] $invoices
     * @return float
     */
    protected function sumInvoiceAmounts(iterable $invoices): float
    {
        $amount = 0.0;
        foreach ($invoices as $invoice) {
            $amount = add($amount, $invoice->getAmountDue());
        }
        return $amount;
    }

    /**
     * @param ClientInvoice[] $invoices
     * @param \App\Billing\Payment $payment
     * @return void
     * @throws \App\Billing\Exceptions\PaymentAmountError
     */
    protected function applyPayment(iterable $invoices, Payment $payment): void
    {
        foreach ($invoices as $invoice) {
            if ($amount = $payment->getAmountAvailable()) {
                if ($amount > $invoice->getAmountDue()) {
                    $amount = $invoice->getAmountDue();
                }
                if ($amount > 0) {
                    $this->paymentApplicator->apply($invoice, $payment, $amount);
                }
            } else {
                break;
            }
        }
        $payment->load('invoices');
    }
}