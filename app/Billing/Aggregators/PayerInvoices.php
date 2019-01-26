<?php
namespace App\Billing\Aggregators;

use App\Billing\ClientInvoice;
use App\Billing\Payer;

class PayerInvoices
{
    /**
     * @var \App\Billing\Payer
     */
    public $payer;

    /**
     * @var \App\Billing\ClientInvoice[]
     */
    public $invoices = [];

    function __construct(Payer $payer, array $invoices = [])
    {
        $this->payer = $payer;
        $this->invoices = $invoices;
    }

    function addInvoice(ClientInvoice $invoice)
    {
        $this->invoices[] = $invoice;
    }
}