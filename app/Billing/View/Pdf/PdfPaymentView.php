<?php
namespace App\Billing\View\Pdf;

use App\Billing\Payment;
use App\Billing\View\Data\PaymentInvoiceData;
use App\Billing\View\PaymentViewStrategy;
use App\Contracts\ContactableInterface;
use Barryvdh\Snappy\PdfWrapper;

class PdfPaymentView implements PaymentViewStrategy
{
    private $filename;
    private $view;
    private $pdfWrapper;

    public function __construct(string $filename, string $view = "statements.payment", PdfWrapper $pdfWrapper = null)
    {
        $this->pdfWrapper = $pdfWrapper ?: app('snappy.pdf.wrapper');
        $this->filename = $filename;
        $this->view = $view;
    }

    /**
     * @param \App\Contracts\ContactableInterface $payer
     * @param \App\Billing\Payment $payment
     * @param \App\Billing\View\Data\PaymentInvoiceData[] $invoiceObjects
     * @return mixed
     * @throws \Throwable
     */
    function generate(ContactableInterface $payer, Payment $payment, array $invoiceObjects)
    {
        $view = view($this->view, compact("payer", "payment", "invoiceObjects"));
        $this->pdfWrapper->loadHTML($view->render());
        return $this->pdfWrapper->download($this->filename);
    }
}