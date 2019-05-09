<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Responses\ErrorResponse;
use App\Responses\Resources\QuickbooksQueueResource;
use App\Responses\SuccessResponse;
use App\Services\Quickbooks\QuickbooksInvoice;
use App\Services\Quickbooks\QuickbooksInvoiceItem;
use App\Services\QuickbooksOnlineService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class QuickbooksQueueController extends Controller
{
    /**
     * Get claims listing.
     *
     * @param Request $request
     * @param ClientInvoiceQuery $invoiceQuery
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, ClientInvoiceQuery $invoiceQuery)
    {
        if ($request->expectsJson()) {
            $invoiceQuery->forRequestedBusinesses();

            if ($request->has('start_date')) {
                $startDate = Carbon::parse($request->start_date)->toDateTimeString();
                $endDate = Carbon::parse($request->end_date)->toDateString() . ' 23:59:59';
                $invoiceQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($request->filled('client_id')) {
                $invoiceQuery->where('client_id', $request->client_id);
            }

            if ($request->filled('payer_id')) {
                $invoiceQuery->whereHas('clientPayer', function ($query) use($request) {
                    $query->where('payer_id', $request->payer_id);
                });
            }

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'payments', 'claim', 'quickbooksInvoice'])->get();

            return QuickbooksQueueResource::collection($invoices);
        }

        return view_component('business-quickbooks-queue', 'Quickbooks Invoice Queue');
    }

    /**
     * Transfer ClientInvoice to Quickbooks.
     *
     * @param ClientInvoice $invoice
     * @return ErrorResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    public function transfer(ClientInvoice $invoice)
    {
        $business = $invoice->client->business;
        $this->authorize('update', $business);

        $connection = $business->quickbooksConnection;
        if (empty($connection)) {
            return new ErrorResponse(401, 'Not connected to the Quickbooks API.');
        }

        $qbInvoice = new QuickbooksInvoice();
        $qbInvoice->date = Carbon::parse($invoice->getDate());
        $qbInvoice->amount = $invoice->getAmount();
        $qbInvoice->invoiceId = $invoice->getName();

        if ($customer = $invoice->client->quickbooksCustomer) {
            $qbInvoice->customerId = $customer->customer_id;
            $qbInvoice->customerName = $customer->name;
        }

        foreach ($invoice->getItems() as $invoiceItem) {
            $lineItem = new QuickbooksInvoiceItem();
            $lineItem->amount = $invoiceItem->total;
            $lineItem->description = $invoiceItem->group;
            $lineItem->itemId = '3';
            $lineItem->itemName = 'Concrete';
            $lineItem->quantity = $invoiceItem->units;
            $lineItem->unitPrice = $invoiceItem->rate;
            $qbInvoice->addItem($lineItem);
        }

        $result = app(QuickbooksOnlineService::class)
            ->setAccessToken($connection->access_token)
            ->createInvoice($qbInvoice->toArray());

        if (empty($result)) {
            return new ErrorResponse(500, 'An error occurred while trying to submit the invoice.  Please try again.');
        }

        $invoice->quickbooksInvoice()->create([
            'client_invoice_id' => $invoice->id,
            'quickbooks_invoice_id' => $result->Id,
        ]);

        $invoice = $invoice->fresh()->load(['client', 'clientPayer.payer', 'payments', 'claim', 'quickbooksInvoice']);
        return new SuccessResponse('Invoice transmitted successfully.', new QuickbooksQueueResource($invoice));
    }
}
