<?php
namespace App\Billing\View;

use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
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
        $clientPayer = $clientInvoice->getClientPayer();
        $business = $client->business;
        $items = $clientInvoice->items;
        $payments = $clientInvoice->payments;

        return $this->generate(
            $viewName ?? 'invoices.client_invoice',
            $clientPayer->isPrivatePay() ? $business : $client,
            $clientPayer->isPrivatePay() ? $client : $clientPayer,
            $clientInvoice,
            $items,
            $payments
        );
    }

    function generateCaregiverInvoice(CaregiverInvoice $caregiverInvoice, ?string $viewName = null)
    {
        $caregiver = $caregiverInvoice->caregiver;
        $items = $caregiverInvoice->items;
        $payments = $caregiverInvoice->deposits;

        return $this->generate(
            $viewName ?? 'invoices.caregiver_invoice',
            $caregiver,
            $caregiver,
            $caregiverInvoice,
            $items,
            $payments
        );
    }

    function generateBusinessInvoice(BusinessInvoice $businessInvoice, ?string $viewName = null)
    {
        $business = $businessInvoice->business;
        $items = $businessInvoice->items;
        $payments = $businessInvoice->deposits;

        return $this->generate(
            $viewName ?? 'invoices.business_invoice',
            $business,
            $business,
            $businessInvoice,
            $items,
            $payments
        );
    }

    function getItemGroups(Collection $items)
    {
        return $items->sortBy('date')->groupBy('group');
    }
}