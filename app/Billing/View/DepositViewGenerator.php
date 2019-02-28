<?php
namespace App\Billing\View;

use App\Billing\Deposit;
use App\Billing\View\Data\DepositInvoiceData;
use App\Contracts\ViewStrategy;

class DepositViewGenerator
{
    protected $strategy;

    function __construct(DepositViewStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    function generate(Deposit $deposit, string $viewName = "statements.deposit")
    {
        $invoiceObjects = [];

        if ($recipient = $deposit->caregiver) {
            foreach($deposit->caregiverInvoices as $invoice) {
                $invoiceObject = new DepositInvoiceData(
                    $invoice,
                    (float) $invoice->pivot->amount_applied
                );
                $invoiceObjects[] = $invoiceObject;
            }
        } else {
            $recipient = $deposit->business;
            foreach($deposit->businessInvoices as $invoice) {
                $invoiceObject = new DepositInvoiceData(
                    $invoice,
                    (float) $invoice->pivot->amount_applied
                );
                $invoiceObjects[] = $invoiceObject;
            }
        }

        return $this->strategy->generate($recipient, $deposit, $invoiceObjects);
    }
}
