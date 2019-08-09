<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Payment;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\ChargedRate;
use App\QuickbooksClientInvoice;
use App\QuickbooksConnection;
use App\QuickbooksService;
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

        /** @var string $timezone */
        $timezone = $client->getTimezone();

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
        if (empty($api)) {
            return new ErrorResponse(500, 'An unexpected error occurred trying to connect to the Quickbooks API.  Please try again.');
        }

        if ($connection->fee_type == QuickbooksConnection::FEE_TYPE_REGISTRY && $invoice->getAmountDue() > 0) {
            return new ErrorResponse(500, 'Invoices must be charged and paid in full in order to transmit registry fees.');
        }

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
        $qbInvoice->invoiceId = $invoice->getName();

        if ($customer = $client->quickbooksCustomer) {
            $qbInvoice->customerId = $customer->customer_id;
            $qbInvoice->customerName = $customer->name;
        } else {
            return new ErrorResponse(401, 'Could not find a Customer Client relationship.');
        }

        /** @var ClientInvoiceItem $invoiceItem */
        foreach ($invoice->getItems() as $invoiceItem) {
            $lineItem = new QuickbooksInvoiceItem();
            $lineItem->quantity = $invoiceItem->units;

            if ($connection->fee_type == QuickbooksConnection::FEE_TYPE_REGISTRY) {
                // Use the provider/registry rates

                if ($invoiceItem->was_split) {
                    // For split invoices, the first time an invoiceable appears
                    // we send the full provider amount, and for subsequent occurrences
                    // we just send zero values.  This allows for the total amount to
                    // be accurate in Quickbooks without over-complicating things.
                    $hasBeenSentAlready = QuickbooksClientInvoice::query()
                        ->whereNotIn('client_invoice_id', [$invoice->id])
                        ->whereHas('clientInvoice', function ($q) use ($invoiceItem) {
                            $q->whereHas('items', function ($q) use ($invoiceItem) {
                                $q->where('invoiceable_id', $invoiceItem->invoiceable_id)
                                    ->where('invoiceable_type', $invoiceItem->invoiceable_type);
                            });
                        })
                        ->exists();

                    if ($hasBeenSentAlready) {
                        $lineItem->unitPrice = floatval(0.00);
                        $lineItem->amount = floatval(0.00);
                    } else {
                        $lineItem->unitPrice = $invoiceItem->getInvoiceable()->getProviderRate();
                        $lineItem->amount = multiply($lineItem->quantity, $lineItem->unitPrice);
                    }
                } else {
                    // For regular invoices, we just send the same provider fee
                    // calculation used when generating the BusinessInvoices.
                    $lineItem->unitPrice = $invoiceItem->getInvoiceable()->getProviderRate();
                    $lineItem->amount = multiply($lineItem->quantity, $lineItem->unitPrice);
                }
            } else {
                // Just use the client rate
                $lineItem->unitPrice = $invoiceItem->rate ?? 0.00;
                $lineItem->amount = $invoiceItem->total ?? 0.00;
            }

            switch ($invoiceItem->invoiceable_type) {
                case 'shifts':
                    // mm/dd/yyyy - ($CaregiverLN, $CaregiverFN) $00:00time to $00:00time $ServiceBreakout
                    // Example Description: 04/22/2019 - (Jones, Steven) 08:00 AM - 10:00 AM RespitCare
                    $shift = $invoiceItem->getShift();
                    $service = $shift->service;
                    $startTime = $shift->checked_in_time->timezone($timezone)->format('h:i A');
                    $endTime = $shift->checked_out_time->timezone($timezone)->format('h:i A');
                    $date = $shift->checked_in_time->timezone($timezone)->format('m/d/Y');
                    $lineItem->serviceDate = $shift->checked_in_time->timezone($timezone)->format('Y-m-d');
                    $lineItem->description = "$date - ({$shift->caregiver->lastname}, {$shift->caregiver->firstname}) $startTime to $endTime {$service->name}";

                    // Use the shift mapping for now.
                    [$lineItem->itemId, $lineItem->itemName] = $this->mapInvoiceItemToService($invoiceItem, $connection, $shift->quickbooks_service_id);
                    break;
                case 'shift_services':
                    $shiftService = $invoiceItem->getShiftService();
                    $shift = $shiftService->shift;
                    $service = $shiftService->service;
                    $startTime = $shift->checked_in_time->timezone($timezone)->format('h:i A');
                    $endTime = $shift->checked_out_time->timezone($timezone)->format('h:i A');
                    $date = $shift->checked_in_time->timezone($timezone)->format('m/d/Y');
                    $lineItem->serviceDate = $shift->checked_in_time->timezone($timezone)->format('Y-m-d');
                    $lineItem->description = "$date - ({$shift->caregiver->lastname}, {$shift->caregiver->firstname}) $startTime to $endTime {$service->name}";

                    // Use the shift mapping for now.
                    [$lineItem->itemId, $lineItem->itemName] = $this->mapInvoiceItemToService($invoiceItem, $connection, $shiftService->quickbooks_service_id);
                    break;
                case 'shift_expenses':
                    $shift = $invoiceItem->invoiceable->getShift();
                    $startTime = $shift->checked_in_time->timezone($timezone)->format('h:i A');
                    $endTime = $shift->checked_out_time->timezone($timezone)->format('h:i A');
                    $date = $shift->checked_in_time->timezone($timezone)->format('m/d/Y');
                    $lineItem->serviceDate = $shift->checked_in_time->timezone($timezone)->format('Y-m-d');
                    $lineItem->description = "$date - ({$shift->caregiver->lastname}, {$shift->caregiver->firstname}) $startTime to $endTime " . $invoiceItem->invoiceable->getItemName('');
                    [$lineItem->itemId, $lineItem->itemName] = $this->mapInvoiceItemToService($invoiceItem, $connection);
                    break;
                default:
                    // Convert from raw line item data.
                    $lineItem->description = $invoiceItem->group;
                    [$lineItem->itemId, $lineItem->itemName] = $this->mapInvoiceItemToService($invoiceItem, $connection);
                    break;
            }
            $qbInvoice->addItem($lineItem);
        }

        $qbInvoice->amount = collect($qbInvoice->lineItems)->reduce(function (float $carry, $item) {
           return add($carry, $item->amount);
        }, floatval(0));

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
     * @param int|null $overrideServiceId
     * @return array
     */
    public function mapInvoiceItemToService(ClientInvoiceItem $item, QuickbooksConnection $connection, ?int $overrideServiceId = null) : array
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
        } else { // Shift Services
            if ($connection->allow_shift_overrides && filled($overrideServiceId)) {
                $service = QuickbooksService::find($overrideServiceId);
                if (empty($service)) {
                    // Service was deleted and not active.
                    $service = $connection->shiftService;
                }
            } else {
                $service = $connection->shiftService;
            }
        }

        return [$service->service_id, $service->name];
    }
}
