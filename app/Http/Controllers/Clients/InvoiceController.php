<?php
namespace App\Http\Controllers\Clients;

use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\HtmlViewStrategy;
use App\Billing\View\InvoiceViewGenerator;
use App\Billing\View\PdfViewStrategy;

class InvoiceController extends BaseController
{
    /**
     * @var \App\Billing\Queries\ClientInvoiceQuery
     */
    protected $invoiceQuery;

    public function __construct(ClientInvoiceQuery $invoiceQuery)
    {
        $this->invoiceQuery = $invoiceQuery;
    }

    public function index()
    {
        $client = $this->client();
        $invoices = $this->invoiceQuery->forClient($client->id)->get();
        return view('clients.invoice_history', compact('client', 'invoices'));
    }

    public function show(ClientInvoice $invoice, string $view = "html")
    {
        $this->authorize('read', $invoice);

        $strategy = new HtmlViewStrategy();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfViewStrategy('invoice-' . str_slug($invoice->getName()) . '.pdf');
        }

        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator->generateClientInvoice($invoice);
    }
}