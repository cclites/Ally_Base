<?php
namespace App\Billing\Actions;

use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Deposit;
use App\Billing\Exceptions\PaymentAmountError;

class ApplyDeposit
{
    public function apply(DepositInvoiceInterface $invoice, Deposit $deposit, ?float $amount = null)
    {
        if ($amount === null) {
            $amount = $deposit->getAmountAvailable();
        }
        elseif ($amount > $deposit->getAmountAvailable()) {
            throw new PaymentAmountError("The amount to apply is more than available.");
        }

        if ($amount > $invoice->getAmountDue()) {
            $amount = $invoice->getAmountDue();
        }

        $invoice->addDeposit($deposit, $amount);

        foreach($invoice->getItems() as $item) {
            if ($item->invoiceable && $item->amount_due) {
                /** @var \App\Billing\Contracts\InvoiceableInterface $invoiceable */
                $invoiceable = $item->invoiceable;
                $allocatedPct = divide($item->amount_due, $invoice->amount);
                $allocatedAmount = multiply($allocatedPct, $amount);
                $invoiceable->addAmountDeposited($deposit, $allocatedAmount);
            }
        }
    }

    public function unapply(DepositInvoiceInterface $invoice, Deposit $deposit)
    {
        // TODO: Unapply functionality
    }

}