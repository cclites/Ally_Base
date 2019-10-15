<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\ChargedRate;
use App\QuickbooksClientInvoice;
use App\QuickbooksConnection;
use App\QuickbooksInvoiceStatus;
use App\QuickbooksService;
use App\Responses\ErrorResponse;
use App\Responses\Resources\QuickbooksQueueResource;
use App\Responses\SuccessResponse;
use App\Services\Quickbooks\QuickbooksOnlineInvoice;
use App\Services\Quickbooks\QuickbooksOnlineInvoiceItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class QuickbooksQueueController extends Controller
{
    /**
     * Get claims listing.
     *
     * @param Request $request
     * @param OnlineClientInvoiceQuery $invoiceQuery
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, OnlineClientInvoiceQuery $invoiceQuery)
    {
        if ($request->expectsJson()) {
            $invoiceQuery->paidInFull()
                ->forRequestedBusinesses();

            if ($request->has('start_date')) {
                $startDate = Carbon::parse($request->start_date)->toDateTimeString();
                $endDate = Carbon::parse($request->end_date)->toDateString() . ' 23:59:59';
                $invoiceQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($request->filled('client_id')) {
                $invoiceQuery->where('client_id', $request->client_id);
            }

            if ($request->filled('payer_id')) {
                $invoiceQuery->whereHas('clientPayer', function ($query) use ($request) {
                    $query->where('payer_id', $request->payer_id);
                });
            }

            $invoices = $invoiceQuery->with(['client', 'clientPayer.payer', 'quickbooksInvoice.statuses'])->get();

            return QuickbooksQueueResource::collection($invoices);
        }

        return view_component('business-quickbooks-queue', 'Quickbooks Invoice Queue');
    }

    /**
     * Transfer ClientInvoice to Quickbooks Online.
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

        /** @var string $timezone */
        $timezone = $client->getTimezone();

        /** @var \App\Business $client */
        $business = $client->business;
        $this->authorize('update', $business);

        /** @var QuickbooksConnection $connection */
        $connection = $business->quickbooksConnection;
        if (empty($connection) || !$connection->isConfigured() || $connection->is_desktop) {
            return new ErrorResponse(401, 'You must be connected to the Quickbooks Online API and have all your settings configured in order to use this feature.  Please visit the Settings > Quickbooks area to manage your Quickbooks configuration.  Note: this includes setting up all service mappings.');
        }

        /** @var \App\Services\QuickbooksOnlineService $api */
        $api = $connection->getApiService();
        if (empty($api)) {
            return new ErrorResponse(500, 'An unexpected error occurred trying to connect to the Quickbooks API.  Please try again.');
        }

        if ($connection->fee_type == QuickbooksConnection::FEE_TYPE_REGISTRY && $invoice->getAmountDue() > 0) {
            return new ErrorResponse(500, 'Invoices must be charged and paid in full in order to transmit registry fees.');
        }

        if (empty($client->quickbooksCustomer)) {
            // Create new customer relationship.
            try {
                [$customerId, $customerName] = $api->createCustomer($client);
            } catch (\Exception $ex) {
                // Handle duplicate customer name errors
                return new ErrorResponse(500, "Could not create customer record for this invoice, customer name already exists.  Please select a client mapping for client {$client->name} in the Quickbooks Settings area.");
            }

            $customer = $client->quickbooksCustomer()->create([
                'business_id' => $business->id,
                'name' => $customerName,
                'customer_id' => $customerId,
            ]);
            $client->update(['quickbooks_customer_id' => $customer->id]);
            $invoice = $invoice->fresh(['client']);
        }

        $qbInvoice = QuickbooksOnlineInvoice::fromClientInvoice($connection, $invoice);

        try
        {
            $result = $api->createInvoice($qbInvoice->toArray());

            if (empty($result)) {
                return new ErrorResponse(500, 'An error occurred while trying to submit the invoice.  Please try again.');
            }

        } catch (\Exception $ex) {
            return new ErrorResponse(500, "An error occurred while trying to submit the invoice: {$ex->getMessage()}.  Please try again.");
        }

        $record = $invoice->quickbooksInvoice()->create([
            'client_invoice_id' => $invoice->id,
            'qb_online_id' => $result->Id,
        ]);

        $record->updateStatus(QuickbooksInvoiceStatus::TRANSFERRED());

        $invoice = $invoice->fresh()->load(['client', 'clientPayer.payer', 'quickbooksInvoice.statuses']);
        return new SuccessResponse('Invoice transmitted successfully.', new QuickbooksQueueResource($invoice));
    }

    /**
     * Add invoice to quickbooks desktop invoice queue.
     *
     * @param ClientInvoice $invoice
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function enqueue(ClientInvoice $invoice)
    {
        /** @var \App\Client $client */
        $client = $invoice->client;

        /** @var \App\Business $client */
        $business = $client->business;
        $this->authorize('update', $business);

        /** @var QuickbooksConnection $connection */
        $connection = $business->quickbooksConnection;
        if (empty($connection) || !$connection->isConfigured() || !$connection->is_desktop) {
            return new ErrorResponse(401, 'You must configure your Quickbooks Desktop connection in order to use this feature.  Please visit the Settings > Quickbooks area to manage your Quickbooks configuration.  Note: this includes setting up all service mappings.');
        }

        if ($connection->fee_type == QuickbooksConnection::FEE_TYPE_REGISTRY && $invoice->getAmountDue() > 0) {
            return new ErrorResponse(500, 'Invoices must be charged and paid in full in order to transmit registry fees.');
        }

        // Ensure there is a qb invoice entry
        if (empty($invoice->quickbooksInvoice)) {
            $invoice->quickbooksInvoice()->create([
                'client_invoice_id' => $invoice->id,
            ]);
        }

        $invoice->fresh()->quickbooksInvoice->updateStatus(QuickbooksInvoiceStatus::QUEUED(), ['errors' => null]);

        $invoice = $invoice->fresh()->load(['client', 'clientPayer.payer', 'quickbooksInvoice.statuses']);
        return new SuccessResponse('Invoice added to queue successfully.', new QuickbooksQueueResource($invoice));
    }

    /**
     * Remove a client invoice from the quickbooks queue.
     *
     * @param ClientInvoice $invoice
     * @return ErrorResponse|SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function dequeue(ClientInvoice $invoice)
    {
        /** @var \App\Client $client */
        $client = $invoice->client;

        /** @var \App\Business $client */
        $business = $client->business;
        $this->authorize('update', $business);

        /** @var QuickbooksConnection $connection */
        $connection = $business->quickbooksConnection;
        if (empty($connection) || !$connection->isConfigured() || !$connection->is_desktop) {
            return new ErrorResponse(401, 'You must configure your Quickbooks Desktop connection in order to use this feature.  Please visit the Settings > Quickbooks area to manage your Quickbooks configuration.  Note: this includes setting up all service mappings.');
        }

        $invoice->quickbooksInvoice->updateStatus(QuickbooksInvoiceStatus::READY());

        $invoice = $invoice->fresh()->load(['client', 'clientPayer.payer', 'quickbooksInvoice.statuses']);
        return new SuccessResponse('Invoice removed from queue successfully.', new QuickbooksQueueResource($invoice));
    }
}
