<?php
namespace App\Billing\Contracts;

use Illuminate\Support\Collection;

interface InvoiceInterface
{
    function getName(): string;
    function getDate(): string;
    function getAmount(): float;
    function getAmountPaid(): float;
    function getAmountDue(): float;
    public function getItems(): Collection;
    public function getItemGroups(): Collection;
}