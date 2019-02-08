<?php
namespace App\Billing\View;

use App\Contracts\ViewStrategy;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Contracts\View\View;

class PdfViewStrategy implements ViewStrategy
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

    /**
     * @param \Illuminate\Contracts\View\View $view
     * @return \Illuminate\Http\Response
     */
    public function generate(View $view)
    {
        $this->pdfWrapper->loadHTML($view->render());
        return $this->pdfWrapper->download($this->filename);
    }
}