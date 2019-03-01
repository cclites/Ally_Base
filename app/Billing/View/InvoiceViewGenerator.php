<?php
namespace App\Billing\View;

use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\InvoiceInterface;
use App\Businesses\NullContact;
use App\Contracts\ViewStrategy;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

class InvoiceViewGenerator
{
    /**
     * @var \App\Contracts\ViewStrategy
     */
    protected $strategy;

    function __construct(InvoiceViewStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    function generate(ContactableInterface $sender, ContactableInterface $recipient, InvoiceInterface $invoice, Collection $payments)
    {
        return $this->strategy->generate($invoice, $sender, $recipient, $payments);
    }

    function generateClientInvoice(ClientInvoice $clientInvoice)
    {
        $client = $clientInvoice->client;
//        $clientPayer = $clientInvoice->getClientPayer();
        $business = $client->business;
        $payments = $clientInvoice->payments;

        return $this->generate(
            $business,
            $client,
            $clientInvoice,
            $payments
        );
    }

    function generateCaregiverInvoice(CaregiverInvoice $caregiverInvoice)
    {
        $caregiver = $caregiverInvoice->caregiver;
        $payments = $caregiverInvoice->deposits;

        return $this->generate(
            new NullContact(),
            $caregiver,
            $caregiverInvoice,
            $payments
        );
    }

    function generateBusinessInvoice(BusinessInvoice $businessInvoice)
    {
        $business = $businessInvoice->business;
        $payments = $businessInvoice->deposits;

        return $this->generate(
            new NullContact(),
            $business,
            $businessInvoice,
            $payments
        );
    }
}