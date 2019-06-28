<?php


namespace App\Billing\View\Html;

use App\Billing\Contracts\InvoiceInterface;
use App\Billing\View\InvoiceViewStrategy;
use App\Contracts\ContactableInterface;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Support\Collection;

class PdfInvoiceView implements InvoiceViewStrategy
{
    private $view;
    private $pdfWrapper;
    private $filename;

    public function __construct(string $filename, string $view, PdfWrapper $pdfWrapper = null)
    {
        $this->pdfWrapper = $pdfWrapper ?: app('snappy.pdf.wrapper');
        $this->filename = $filename;
        $this->view = $view;
    }


    public function generate(
        InvoiceInterface $invoice,
        ContactableInterface $sender,
        ContactableInterface $recipient,
        ContactableInterface $subject,
        Collection $payments
    ) {
        $itemGroups = $invoice->getItemGroups();
        $view = view($this->view, compact('invoice', 'sender', 'recipient', 'subject', 'payments', 'itemGroups'));
        $this->pdfWrapper->loadHTML($view->render());
        return $this->pdfWrapper->download($this->filename);
    }
}