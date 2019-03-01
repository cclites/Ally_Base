<?php
namespace App\Billing\View\Pdf;

use App\Billing\Deposit;
use App\Billing\View\DepositViewStrategy;
use App\Contracts\ContactableInterface;
use Barryvdh\Snappy\PdfWrapper;

class PdfDepositView implements DepositViewStrategy
{
    private $filename;
    private $view;
    private $pdfWrapper;

    public function __construct(string $filename, string $view = "statements.deposit", PdfWrapper $pdfWrapper = null)
    {
        $this->pdfWrapper = $pdfWrapper ?: app('snappy.pdf.wrapper');
        $this->filename = $filename;
        $this->view = $view;
    }

    /**
     * @param \App\Contracts\ContactableInterface $recipient
     * @param \App\Billing\Deposit $deposit
     * @param \App\Billing\View\Data\DepositInvoiceData[] $invoiceObjects
     * @return mixed
     */
    function generate(ContactableInterface $recipient, Deposit $deposit, array $invoiceObjects)
    {
        $view = view($this->view, compact("recipient", "deposit", "invoiceObjects"));
        $this->pdfWrapper->loadHTML($view->render());
        return $this->pdfWrapper->download($this->filename);
    }
}