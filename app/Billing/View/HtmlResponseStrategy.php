<?php
namespace App\Billing\View;

use App\Billing\Contracts\InvoiceInterface;
use App\Billing\Contracts\InvoiceViewStrategy;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Contracts\View\View;

class HtmlResponseStrategy implements InvoiceViewStrategy
{

    /**
     * @param \App\Billing\Contracts\InvoiceInterface $invoice
     * @param \Illuminate\Contracts\View\View $view
     * @return \Illuminate\Http\Response
     */
    public function generate(InvoiceInterface $invoice, View $view)
    {
        return response($view);
    }
}