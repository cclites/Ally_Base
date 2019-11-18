<?php
namespace App\Services\Quickbooks;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\QuickbooksClientInvoice;
use App\QuickbooksConnection;
use App\QuickbooksService;
use App\Responses\ErrorResponse;
use Carbon\Carbon;

class QuickbooksOnlineInvoice
{
    /**
     * @var string
     */
    public $customerId;

    /**
     * @var string
     */
    public $customerName;

    /**
     * @var \Illuminate\Support\Collection|QuickbooksOnlineInvoiceItem[]
     */
    public $lineItems = [];

    /**
     * @var \Carbon\Carbon
     */
    public $date;

    /**
     * @var string
     */
    public $invoiceId;

    /**
     * The QuickbooksClientInvoice id
     *
     * @var null|int
     */
    public $qbInvoiceId;

    /**
     * Add a line item.
     *
     * @param QuickbooksOnlineInvoiceItem $item
     */
    public function addItem(QuickbooksOnlineInvoiceItem $item) : void
    {
        $this->lineItems[] = $item;
    }

    /**
     * Convert the object to a Quickbooks API Invoice.
     * This method is exclusively for the QB Online API, any
     * modifications here may break the API request.
     *
     * @return array
     */
    public function toArray() : array
    {
        $items = [];
        for ($i = 1; $i <= count($this->lineItems); $i++)
        {
            $item = $this->lineItems[$i-1]->toArray();
            $item['LineNum'] = $i;
            $items[] = $item;
        }

        return [
            'DocNumber' => $this->invoiceId,
            'TxnDate' => $this->date->format('Y-m-d'),
//            'DueDate' => $this->date->format('Y-m-d'),
            'Line' => $items,
            'CustomerRef' => [
                'value' => $this->customerId,
                'name' => $this->customerName
            ]
        ];
    }

    /**
     * Convert invoice into Quickbooks Desktop invoice array.
     *
     * @return array
     */
    public function toDesktopArray() : array
    {
        $items = collect($this->lineItems)
            ->map(function (QuickbooksOnlineInvoiceItem $item) {
                return $item->toDesktopArray();
            })
            ->toArray();

        return [
            'ID' => $this->invoiceId,
            'QuickbooksInvoiceID' => $this->qbInvoiceId,
            'Date' => $this->date->format('Y-m-d'),
            'Items' => $items,
            'CustomerID' => $this->customerId,
            'CustomerName' => $this->customerName
        ];
    }

    /**
     * Create a QuickbooksOnlineInvoice from a ClientInvoice.  This method
     * can be used for both online and desktop versions of Quickbooks.
     *
     * @param QuickbooksConnection $connection
     * @param ClientInvoice $invoice
     * @param bool $requiredCustomerMap
     * @return QuickbooksOnlineInvoice
     * @throws \Exception
     */
    public static function fromClientInvoice(QuickbooksConnection $connection, ClientInvoice $invoice, bool $requiredCustomerMap = true) : QuickbooksOnlineInvoice
    {
        /** @var \App\Client $client */
        $client = $invoice->client;

        /** @var string $timezone */
        $timezone = $client->getTimezone();

        $qbInvoice = new QuickbooksOnlineInvoice();
        $qbInvoice->date = Carbon::parse($invoice->getDate());
        $qbInvoice->invoiceId = $invoice->getName();
        $qbInvoice->qbInvoiceId = optional($invoice->quickbooksInvoice)->id;

        if ($customer = $client->quickbooksCustomer) {
            $qbInvoice->customerId = $customer->customer_id;
            $qbInvoice->customerName = $customer->name;
        } else {
            if ($requiredCustomerMap) {
                throw new \Exception('Could not find a Customer Client relationship.');
            }
            if ($connection->getNameFormat() == 'last_first') {
                $qbInvoice->customerName = $client->nameLastFirst();
            }
            else {
                $qbInvoice->customerName = $client->name;
            }
        }

        /** @var ClientInvoiceItem $invoiceItem */
        foreach ($invoice->getItems() as $invoiceItem) {
            $lineItem = new QuickbooksOnlineInvoiceItem();
            $lineItem->quantity = $invoiceItem->units;

            if ($connection->getFeeType($client) == QuickbooksConnection::FEE_TYPE_REGISTRY) {
                // Use the provider/registry rates

                if (empty($invoiceItem->getInvoiceable())) {
                    // Adjustments
                    $lineItem->unitPrice = floatval(0.00);
                    $lineItem->amount = floatval(0.00);
                }
                else if ($invoiceItem->was_split) {
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
                    [$lineItem->itemId, $lineItem->itemName] = self::mapInvoiceItemToService($invoiceItem, $connection, $shift->quickbooks_service_id);
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
                    [$lineItem->itemId, $lineItem->itemName] = self::mapInvoiceItemToService($invoiceItem, $connection, $shiftService->quickbooks_service_id);
                    break;
                case 'shift_expenses':
                    $shift = $invoiceItem->invoiceable->getShift();
                    $startTime = $shift->checked_in_time->timezone($timezone)->format('h:i A');
                    $endTime = $shift->checked_out_time->timezone($timezone)->format('h:i A');
                    $date = $shift->checked_in_time->timezone($timezone)->format('m/d/Y');
                    $lineItem->serviceDate = $shift->checked_in_time->timezone($timezone)->format('Y-m-d');
                    $lineItem->description = "$date - ({$shift->caregiver->lastname}, {$shift->caregiver->firstname}) $startTime to $endTime " . $invoiceItem->invoiceable->getItemName('');
                    [$lineItem->itemId, $lineItem->itemName] = self::mapInvoiceItemToService($invoiceItem, $connection);
                    break;
                default:
                    // Convert from raw line item data.
                    $lineItem->description = $invoiceItem->group;
                    [$lineItem->itemId, $lineItem->itemName] = self::mapInvoiceItemToService($invoiceItem, $connection);
                    break;
            }
            $qbInvoice->addItem($lineItem);
        }

        $qbInvoice->amount = collect($qbInvoice->lineItems)->reduce(function (float $carry, $item) {
            return add($carry, $item->amount);
        }, floatval(0));

        return $qbInvoice;
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
    public static function mapInvoiceItemToService(ClientInvoiceItem $item, QuickbooksConnection $connection, ?int $overrideServiceId = null): array
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