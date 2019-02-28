<?php
namespace App\Billing\View\Data;

use App\Billing\ClientInvoice;
use Illuminate\Support\Collection;

class PaymentInvoiceData
{
    private $invoice;
    private $amountApplied;
    private $itemGroups;

    public function __construct(ClientInvoice $invoice, float $amountApplied)
    {
        $this->invoice = $invoice;
        $this->amountApplied = $amountApplied;
        $this->itemGroups = $invoice->getItemGroups();
    }

    function invoice(): ClientInvoice
    {
        return $this->invoice;
    }

    function amountApplied(): float
    {
        return $this->amountApplied;
    }

    function itemGroups(): Collection
    {
        return $this->itemGroups;
    }
}