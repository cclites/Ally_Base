<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Queries\ClientInvoiceQuery;
use App\QuickbooksConnection;
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
     * @throws \Exception
     */
    public function transfer(ClientInvoice $invoice)
    {
        /** @var \App\Client $client */
        $client = $invoice->client;

        /** @var \App\Business $client */
        $business = $client->business;
        $this->authorize('update', $business);

        /** @var QuickbooksConnection $connection */
        $connection = $business->quickbooksConnection;
        if (empty($connection) || ! $connection->isConfigured()) {
            return new ErrorResponse(401, 'You must be connected to the Quickbooks API and have all your settings configured in order to use this feature.  Please visit the Settings > Quickbooks area to manage your Quickbooks configuration.');
        }

        /** @var \App\Services\QuickbooksOnlineService $api */
        $api = $connection->getApiService();
        if (empty($client->quickbooksCustomer)) {
            // Create new customer relationship.
            [$customerId, $customerName] = $api->createCustomer($client);
            $customer = $client->quickbooksCustomer()->create([
                'business_id' => $business->id,
                'name' => $customerName,
                'customer_id' => $customerId,
            ]);
            $client->update(['quickbooks_customer_id' => $customer->id]);
            $client = $client->fresh();
        }

        $qbInvoice = new QuickbooksInvoice();
        $qbInvoice->date = Carbon::parse($invoice->getDate());
        $qbInvoice->amount = $invoice->getAmount();
        $qbInvoice->invoiceId = $invoice->getName();

        if ($customer = $client->quickbooksCustomer) {
            $qbInvoice->customerId = $customer->customer_id;
            $qbInvoice->customerName = $customer->name;
        } else {
            return new ErrorResponse(401, 'Could not find a Customer Client relationship.');
        }

        foreach ($invoice->getItems() as $invoiceItem) {
            $lineItem = new QuickbooksInvoiceItem();
            $lineItem->amount = $invoiceItem->total;
            $lineItem->description = $invoiceItem->group;
            [$lineItem->itemId, $lineItem->itemName] = $this->mapInvoiceItemToService($invoiceItem, $connection);

            $lineItem->quantity = $invoiceItem->units;
            $lineItem->unitPrice = $invoiceItem->rate;
            $qbInvoice->addItem($lineItem);
        }

        $result = $api->createInvoice($qbInvoice->toArray());
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

    /**
     * Map invoice line item into the service from the
     * Quickbooks connection configuration.
     *
     * @param ClientInvoiceItem $item
     * @param QuickbooksConnection $connection
     * @return array
     */
    public function mapInvoiceItemToService(ClientInvoiceItem $item, QuickbooksConnection $connection) : array
    {
        $service = null;
        if ($item->name == 'Manual Adjustment') {
            $service = $connection->adjustmentService;
        } else if ($item->name == 'Mileage') {
            $service = $connection->mileageService;
        } else if ($item->name == 'Other Expenses') {
            $service = $connection->expenseService;
        } else if ($item->name == 'Refund') {
            $service = $connection->refundService;
        } else {
            $service = $connection->shiftService;
        }

        return [$service->service_id, $service->name];
    }
}
