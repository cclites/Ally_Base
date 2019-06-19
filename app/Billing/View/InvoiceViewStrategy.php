<?php
namespace App\Billing\View;

use App\Billing\Contracts\InvoiceInterface;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

interface InvoiceViewStrategy
{
    public function generate(InvoiceInterface $invoice, ContactableInterface $sender, ContactableInterface $recipient, ContactableInterface $subject, Collection $payments);
}