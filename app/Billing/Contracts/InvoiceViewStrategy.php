<?php
namespace App\Billing\Contracts;

use Illuminate\Contracts\View\View;

interface InvoiceViewStrategy
{
    public function generate(InvoiceInterface $invoice, View $view);
}