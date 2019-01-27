<?php
namespace App\Billing\Contracts;

use Illuminate\Contracts\View\View;

interface InvoiceViewStrategy
{
    /**
     * @param \App\Billing\Contracts\InvoiceInterface $invoice
     * @param \Illuminate\Contracts\View\View $view
     * @return \Illuminate\Http\Response
     */
    public function generate(InvoiceInterface $invoice, View $view);
}