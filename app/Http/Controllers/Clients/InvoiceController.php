<?php
namespace App\Http\Controllers\Clients;

use App\Billing\ClientInvoice;
use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;

class InvoiceController extends BaseController
{
    /**
     * @var \App\Billing\Queries\OnlineClientInvoiceQuery
     */
    protected $invoiceQuery;

    public function __construct(OnlineClientInvoiceQuery $invoiceQuery)
    {
        $this->invoiceQuery = $invoiceQuery;
    }

    public function index()
    {
        $client = $this->client();
        $invoices = $this->invoiceQuery->forClient($client->id)->get();
        return view('clients.invoice_history', compact('client', 'invoices'));
    }

    public function show(ClientInvoice $invoice, string $view = InvoiceViewFactory::HTML_VIEW)
    {
        $this->authorize('read', $invoice);

        \Log::info(json_encode($invoice->clientPayer));

        $strategy = InvoiceViewFactory::create($invoice, $view);
        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator->generateClientInvoice($invoice);
    }
}