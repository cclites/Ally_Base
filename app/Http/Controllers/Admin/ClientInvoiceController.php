<?php
namespace App\Http\Controllers\Admin;

use App\AdminPin;
use App\Billing\ClientInvoice;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\BusinessChain;
use App\Client;
use App\Http\Controllers\Controller;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\ClientInvoice as ClientInvoiceResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientInvoiceController extends Controller
{
    public function index(Request $request, OnlineClientInvoiceQuery $invoiceQuery)
    {
        if ($request->expectsJson()) {
            if ($request->filled('paid')) {
                if ($request->paid) {
                    $invoiceQuery->paidInFull();
                } else {
                    $invoiceQuery->notPaidInFull();
                }
            }

            if($clientId = $request->input('client_id')){
                $invoiceQuery->where('client_id', $clientId);
            }

            if ($businessId = $request->input('business_id')) {
                $invoiceQuery->forBusiness($businessId);
            }

            if ($chainId = $request->input('chain_id')) {
                $invoiceQuery->forBusinessChain(BusinessChain::findOrFail($chainId));
            }

            if ($request->has('start_date')) {
                $startDate = Carbon::parse($request->start_date)->toDateTimeString();
                $endDate = Carbon::parse($request->end_date)->toDateString() . ' 23:59:59';
                $invoiceQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $invoices = $invoiceQuery->with(['client', 'client.user.paymentHold', 'client.business', 'client.business.chain', 'clientPayer.payer', 'payments', 'items', 'claim'])
            ->get()
            ->map(function(ClientInvoice $invoice){
                $invoice['has_partial_payment'] = $invoice->getHasPartialPayment();
                return $invoice;
            });

            return ClientInvoiceResponse::collection($invoices);
        }

        $chains = BusinessChain::ordered()->get();
        return view_component('admin-client-invoices', 'Client Invoices', compact('chains'));
    }

    public function generate(Request $request, ClientInvoiceGenerator $clientInvoiceGenerator)
    {
        $request->validate([
            'chain_id' => 'required|exists:business_chains,id',
        ]);

        $invoices = [];
        $chain = BusinessChain::findOrFail($request->input('chain_id'));
        $businessIds = $chain->businesses()->pluck('id')->toArray();
//        $timezone = Timezone::getTimezone($businessIds[0]) ?? 'America/New_York';
//        $endDateUtc = Carbon::now($timezone)->startOfWeek()->subSecond()->setTimezone('UTC');
        $endDateUtc = Carbon::now('UTC');

        $errors = [];
        $clients = Client::active()->whereIn('business_id', $businessIds)->get();
        foreach($clients as $client) {
            $generator = clone $clientInvoiceGenerator;
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

        return new CreatedResponse(count($invoices) . ' invoices were created.', [
            'invoices' => $invoices,
            'errors' => $errors,
        ]);
    }

    public function show(ClientInvoice $invoice, string $view = InvoiceViewFactory::HTML_VIEW)
    {
        $strategy = InvoiceViewFactory::create($invoice, $view);
        $viewGenerator = new InvoiceViewGenerator($strategy);
        return $viewGenerator->generateClientInvoice($invoice);
    }

    public function update( Request $request, ClientInvoice $invoice )
    {
        $data = $request->validate([

            'notes' => 'required|max:255'
        ]);

        if( $invoice->update( $request->toArray() ) ) return new SuccessResponse( 'invoice has been updatd.');

        return new ErrorResponse( 500, 'invoice could not be updated.');

    }

    public function destroy(ClientInvoice $invoice)
    {
        if (! AdminPin::verify(request()->pin, 'un-invoice')) {
            return new ErrorResponse(400, "Invalid PIN.");
        }

        if ($invoice->payments()->exists()) {
            return new ErrorResponse(400, "This invoice cannot be removed because it has payments assigned.");
        }

        if (filled($invoice->claimInvoice)) {
            return new ErrorResponse(400, "This invoice cannot be removed because it has a Claim attached.");
        }

        if ($invoice->delete()) {
            return new SuccessResponse("The invoice has been removed.");
        }

        return new ErrorResponse(500, "Unable to remove invoice.");
    }
}