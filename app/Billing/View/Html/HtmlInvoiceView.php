<?php


namespace App\Billing\View\Html;


use App\Billing\Contracts\InvoiceInterface;
use App\Billing\View\InvoiceViewStrategy;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

class HtmlInvoiceView implements InvoiceViewStrategy
{
    private $view;

    function __construct(string $view)
    {
        $this->view = $view;
    }

    public function generate(
        InvoiceInterface $invoice,
        ContactableInterface $sender,
        ContactableInterface $recipient,
        Collection $payments
    ) {
        $itemGroups = $invoice->getItemGroups();
        return response(view($this->view, compact('invoice', 'sender', 'recipient', 'payments', 'itemGroups')));
    }
}