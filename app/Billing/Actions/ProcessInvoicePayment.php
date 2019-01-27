<?php
namespace App\Billing\Actions;

use App\Billing\ClientInvoice;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;

class ProcessInvoicePayment
{
    /**
     * @var \App\Billing\Actions\ProcessPayment
     */
    protected $paymentProcessor;


    public function __construct(ProcessPayment $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }


    /**
     * @param \App\Billing\ClientInvoice $invoice
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy|null $strategy
     * @param float|null $amount
     * @return \App\Billing\Payment
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function payInvoice(ClientInvoice $invoice, ?PaymentMethodStrategy $strategy = null, ?float $amount = null): Payment
    {
        if ($amount === null) {
            $amount = $invoice->getAmountDue();
        }

        $payer = $invoice->getPayer();
        $payment = $this->paymentProcessor->charge($payer, $strategy, $amount);
        if (!$payment) {
            throw new PaymentMethodError("Unable to receive payment for invoice.");
        }

        $invoice->addPayment($payment, $payment->amount);
        return $payment;
    }

    /**
     * @param ClientInvoice[] $invoices
     * @param \App\Billing\Payer $payer
     * @param \App\Billing\Payments\Contracts\PaymentMethodStrategy $strategy
     * @return \App\Billing\Payment
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function payInvoices(iterable $invoices, Payer $payer, PaymentMethodStrategy $strategy): Payment
    {
        $amount = 0;
        foreach($invoices as $invoice) {
            $amount = add($amount, $invoice->getAmountDue());
        }

        $payment = $this->paymentProcessor->charge($payer, $strategy, $amount);
        if (!$payment) {
            throw new PaymentMethodError("Unable to receive payment for invoices.");
        }

        foreach($invoices as $invoice) {
            $invoice->addPayment($payment, $invoice->getAmountDue());
        }

        return $payment;
    }
}