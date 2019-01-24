<?php
namespace App\Billing\View;

use App\Billing\Contracts\InvoiceInterface;
use App\Billing\Contracts\InvoiceViewStrategy;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Contracts\View\View;

class PDFStrategy implements InvoiceViewStrategy
{
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var PdfWrapper
     */
    private $pdfWrapper;

    public function __construct(string $filename, PdfWrapper $pdfWrapper = null)
    {
        $this->pdfWrapper = $pdfWrapper ?: app('snappy.pdf.wrapper');
        $this->filename = $filename;
    }

    public function generate(InvoiceInterface $invoice, View $view)
    {
        $this->pdfWrapper->loadHTML($view->render());
        $this->pdfWrapper->download($this->filename);
    }
}