<?php
namespace App\Billing\View;

use App\Billing\Contracts\InvoiceInterface;
use App\Billing\Contracts\InvoiceViewStrategy;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Contracts\View\View;

class HtmlResponseStrategy implements InvoiceViewStrategy
{

    public function generate(InvoiceInterface $invoice, View $view)
    {
        return response($view);
    }
}