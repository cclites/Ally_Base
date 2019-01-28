<?php
namespace App\Billing\Actions;

use App\Billing\ClientInvoice;
use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Payment;

class ApplyPayment
{
    function apply(ClientInvoice $invoice, Payment $payment, ?float $amount = null)
    {
        if ($amount === null) {
            $amount = $payment->getAmountAvailable();
        }
        elseif ($amount > $payment->getAmountAvailable()) {
            throw new PaymentAmountError("The amount to apply is more than available.");
        }

        if ($amount > $invoice->getAmountDue()) {
            $amount = $invoice->getAmountDue();
        }

        $invoice->addPayment($payment, $amount);

        // Send amount charged with allocated ally fees back to invoiceables
        $allyFee = multiply(divide($payment->amount, $amount), $payment->getAllyFee());
        foreach($invoice->items as $item) {
            if ($item->invoiceable) {
                $allocatedPct = divide($item->amount_due, $invoice->amount);
                $allocatedAmount = multiply($allocatedPct, $amount);
                $allocatedFee = multiply($allocatedPct, $allyFee);
                $item->invoiceable->addAmountCharged($payment, $allocatedAmount, $allocatedFee);
            }
        }
    }

    function unapply(ClientInvoice $invoice, Payment $payment)
    {
        // TODO: Unapply functionality, needs to remove related allocated ally fees from the product
    }
}