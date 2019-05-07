<?php
namespace App\Billing\Events;

use App\Billing\Contracts\InvoiceableInterface;

interface InvoiceableEvent
{
    public function getInvoiceable(): InvoiceableInterface;
}