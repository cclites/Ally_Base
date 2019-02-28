<?php
namespace App\Billing\View\Data;

use App\Billing\Contracts\DepositInvoiceInterface;
use Illuminate\Support\Collection;

class DepositInvoiceData
{
    private $invoice;
    private $amountApplied;
    private $itemGroups;

    public function __construct(DepositInvoiceInterface $invoice, float $amountApplied)
    {
        $this->invoice = $invoice;
        $this->amountApplied = $amountApplied;
        $this->itemGroups = $invoice->getItemGroups();
    }

    function invoice(): DepositInvoiceInterface
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