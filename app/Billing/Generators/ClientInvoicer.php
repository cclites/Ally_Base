<?php
namespace App\Billing\Generators;

use App\Client;
use App\Billing\ClientPayer;

class ClientInvoicer extends BaseInvoicer
{
    public function __construct()
    {

    }

    public function generateAll(Client $client)
    {
        // Collect invoiceables, collect payers, generate invoices
    }

    public function validatePayers(Client $client)
    {
        // Make sure the payer configuration is sound before attempting to generate invoices
    }

    public function generateInvoice(Client $client, ClientPayer $clientPayer)
    {

    }
}