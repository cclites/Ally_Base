<?php
namespace App\Billing\View;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\InvoiceInterface;
use App\Contracts\ViewStrategy;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

class InvoiceViewGenerator
{
    /**
     * @var \App\Contracts\ViewStrategy
     */
    protected $strategy;

    function __construct(ViewStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    function generate(string $viewName, ContactableInterface $sender, ContactableInterface $recipient, InvoiceInterface $invoice, Collection $items, Collection $payments)
    {
        $itemGroups = $this->getItemGroups($items);
        $view = view($viewName, compact('sender', 'recipient', 'invoice', 'itemGroups', 'payments'));
        return $this->strategy->generate($view);
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
            $payer->isPrivatePay() ? $business : $client,
            $payer->isPrivatePay() ? $client : $payer,
            $clientInvoice,
            $items,
            $payments
        );
    }

    function getItemGroups(Collection $items)
    {
        return $items->sortBy('date')->groupBy('group');
    }
}