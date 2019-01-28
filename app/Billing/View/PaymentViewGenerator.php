<?php
namespace App\Billing\View;

use App\Billing\Payment;
use App\Contracts\ContactableInterface;
use App\Contracts\ViewStrategy;

class PaymentViewGenerator
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

    function generate(Payment $payment, string $viewName = "statements.payment")
    {
        $invoiceObjects = [];
        foreach($payment->invoices as $invoice) {
            $invoiceObject = new PaymentInvoiceObject();
            $invoiceObject->invoice = $invoice;
            $invoiceObject->amountApplied = (float) $invoice->pivot->amount_applied;
            $invoiceObject->itemGroups = $this->invoiceView->getItemGroups($invoice->items);
            $invoiceObjects[] = $invoiceObject;
        }

        $payer = $payment->payer;

        $view = view($viewName, compact('payment', 'payer', 'invoiceObjects'));
        return $this->strategy->generate($view);
    }
}

class PaymentInvoiceObject
{
    /**
     * @var \App\Billing\ClientInvoice
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