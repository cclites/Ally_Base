<?php
namespace App\Billing\View;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\InvoiceInterface;
use App\Billing\Contracts\InvoiceViewStrategy;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

class InvoiceViewGenerator
{
    /**
     * @var \App\Billing\Contracts\InvoiceViewStrategy
     */
    protected $strategy;

    function __construct(InvoiceViewStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    function generate(string $viewName, ContactableInterface $sender, ContactableInterface $recipient, InvoiceInterface $invoice, Collection $items, Collection $payments)
    {
        $itemGroups = $items->sortBy('date')->groupBy('group');
        $view = view($viewName, compact('sender', 'recipient', 'invoice', 'itemGroups', 'payments'));
        return $this->strategy->generate($invoice, $view);
    }

    function generateClientInvoice(ClientInvoice $clientInvoice, ?string $viewName = null)
    {
        $client = $clientInvoice->client;
        $payer = $clientInvoice->payer;
        $business = $client->business;
        $items = $clientInvoice->items;
        $payments = $clientInvoice->payments;

        return $this->generate(
            $viewName ?? 'invoices.client_invoice',
            $business,
            $payer->isPrivatePay() ? $client : $payer,
            $clientInvoice,
            $items,
            $payments
        );
    }
}