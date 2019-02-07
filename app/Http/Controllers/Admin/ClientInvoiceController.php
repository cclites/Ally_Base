<?php
namespace App\Http\Controllers\Admin;

use App\Billing\ClientInvoice;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\HtmlViewStrategy;
use App\Billing\View\InvoiceViewGenerator;
use App\Billing\View\PdfViewStrategy;
use App\BusinessChain;
use App\Businesses\Timezone;
use App\Client;
use App\Http\Controllers\Controller;
use App\Responses\CreatedResponse;
use App\Responses\Resources\ClientInvoice as ClientInvoiceResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientInvoiceController extends Controller
{
    public function index(Request $request, ClientInvoiceQuery $invoiceQuery)
    {
        if ($request->has('paid')) {
            if ($request->paid) {
                $invoiceQuery->paidInFull();
            } else {
                $invoiceQuery->notPaidInFull();
            }
        }

        if ($businessId = $request->input('business_id')) {
            $invoiceQuery->forBusiness($businessId);
        }

        if ($chainId = $request->input('chain_id')) {
            $invoiceQuery->forBusinessChain(BusinessChain::findOrFail($chainId));
        }

        $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments'])->get();

        return ClientInvoiceResponse::collection($invoices);
    }

    public function generate(Request $request, ClientInvoiceGenerator $generator)
    {
        $request->validate([
            'chain_id' => 'required|exists:business_chains,id',
        ]);

        \DB::beginTransaction();

        $invoices = [];
        $chain = BusinessChain::findOrFail($request->input('chain_id'));
        $businessIds = $chain->businesses()->pluck('id')->toArray();
//        $timezone = Timezone::getTimezone($businessIds[0]) ?? 'America/New_York';
//        $endDateUtc = Carbon::now($timezone)->startOfWeek()->subSecond()->setTimezone('UTC');
        $endDateUtc = Carbon::now('UTC');

        $errors = [];
        $clients = Client::active()->whereIn('business_id', $businessIds)->get();
        foreach($clients as $client) {
            try {
                $created = $generator->generateAll($client, $endDateUtc);
                foreach($created as $invoice) {
                    $invoices[] = $invoice;
                }
            }
            catch(\Exception $e) {
                $errors[] = [
                    'client' => $client,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ];
            }
        }

        \DB::commit();

        return new CreatedResponse(count($invoices) . ' invoices were created.', [
            'invoices' => $invoices,
            'errors' => $errors,
        ]);
    }

    public function show(ClientInvoice $invoice, string $view = "html")
    {
        $strategy = new HtmlViewStrategy();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfViewStrategy('invoice-' . str_slug($invoice->getName()) . '.pdf');
        }

        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator->generateClientInvoice($invoice);
    }
}