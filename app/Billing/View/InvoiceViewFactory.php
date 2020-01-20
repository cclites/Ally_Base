<?php
namespace App\Billing\View;

use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\InvoiceInterface;
use App\Billing\View\Html\HtmlInvoiceView;
use App\Billing\View\Html\PdfInvoiceView;

/**
 * Class InvoiceViewFactory
 * Factory for producing invoice view strategies
 *
 *
 * @package App\Billing\View
 */
class InvoiceViewFactory
{
    const HTML_VIEW = "html";
    const PDF_VIEW = "pdf";

    private static $invoiceTypes = [

        CaregiverInvoice::class,
        ClientInvoice::class,
        BusinessInvoice::class,
    ];

    private static $viewTypes = [

        self::HTML_VIEW,
        self::PDF_VIEW,
    ];

    public static function create(InvoiceInterface $invoice, string $viewType = 'html', bool $batch = false): InvoiceViewStrategy
    {
        if (!in_array(get_class($invoice), self::$invoiceTypes)) {
            throw new \InvalidArgumentException("The provided invoice type is invalid.");
        }

        if (!in_array($viewType, self::$viewTypes)) {
            throw new \InvalidArgumentException("The provided view type is invalid.");
        }

        switch(get_class($invoice)) {
            case BusinessInvoice::class:
                $view = 'invoices.business_invoice';
                break;
            case CaregiverInvoice::class:
                $view = 'invoices.caregiver_invoice';
                break;
            default:
                $view = 'invoices.client_invoice' . ($batch ? '_nude' : '');
        }

        switch($viewType) {
            case self::PDF_VIEW:
                return new PdfInvoiceView($invoice->getName() . '.pdf', $view);
            default:
                return new HtmlInvoiceView($view);
        }
    }
}