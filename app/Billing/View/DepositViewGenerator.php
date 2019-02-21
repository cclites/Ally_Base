<?php
namespace App\Billing\View;

use App\Billing\Deposit;
use App\Contracts\ViewStrategy;

class DepositViewGenerator
{
    /**
     * @var \App\Contracts\ViewStrategy
     */
    protected $strategy;
    /**
     * @var \App\Billing\View\InvoiceViewGenerator
     */
    protected $invoiceView;

    function __construct(ViewStrategy $strategy, InvoiceViewGenerator $invoiceView = null)
    {
        $this->strategy = $strategy;
        $this->invoiceView = $invoiceView ?: new InvoiceViewGenerator($strategy);
    }

    function generate(Deposit $deposit, string $viewName = "statements.deposit")
    {
        $invoiceObjects = [];

        if ($recipient = $deposit->caregiver) {
            foreach($deposit->caregiverInvoices as $invoice) {
                $invoiceObject = new DepositInvoiceObject();
                $invoiceObject->invoice = $invoice;
                $invoiceObject->amountApplied = (float) $invoice->pivot->amount_applied;
                $invoiceObject->itemGroups = $this->invoiceView->getItemGroups($invoice->items);
                $invoiceObjects[] = $invoiceObject;
            }
        } else {
            $recipient = $deposit->business;
            foreach($deposit->businessInvoices as $invoice) {
                $invoiceObject = new DepositInvoiceObject();
                $invoiceObject->invoice = $invoice;
                $invoiceObject->amountApplied = (float) $invoice->pivot->amount_applied;
                $invoiceObject->itemGroups = $this->invoiceView->getItemGroups($invoice->items);
                $invoiceObjects[] = $invoiceObject;
            }
        }

        $view = view($viewName, compact('recipient', 'deposit', 'invoiceObjects'));
        return $this->strategy->generate($view);
    }
}

class DepositInvoiceObject
{
    /**
     * @var \App\Billing\Contracts\DepositInvoiceInterface
     */
    public $invoice;

    /**
     * @var float
     */
    public $amountApplied;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $itemGroups;
}