<?php

namespace App\Http\Controllers\Clients;

use App\Billing\Payer;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\InvoiceViewGenerator;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\ClientInvoice;

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
        $invoices = $this->invoiceQuery->forClient($client->id, false)
            ->whereHas('clientPayer', function ($q) {
                $q->whereIn('payer_id', [Payer::PRIVATE_PAY_ID, Payer::OFFLINE_PAY_ID]);
            })
            ->get();
        return view('clients.invoice_history', compact('client', 'invoices'));
    }

    public function show(ClientInvoice $invoice, string $view = InvoiceViewFactory::HTML_VIEW)
    {
        $this->authorize('read', $invoice);

        $strategy = InvoiceViewFactory::create($invoice, $view);
        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator->generateClientInvoice($invoice);
    }
}