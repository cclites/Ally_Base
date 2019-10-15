<?php

namespace App\Services\Quickbooks;

use App\Shift;

class QuickbooksOnlineInvoiceItem
{
    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $itemId;

    /**
     * @var string
     */
    public $itemName;

    /**
     * @var string
     */
    public $serviceDate;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var float
     */
    public $quantity;

    /**
     * Convert object to a Quickbooks API SalesItemLineDetail.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'Description' => $this->description,
            'Amount' => $this->amount,
            'DetailType' => 'SalesItemLineDetail',
            'SalesItemLineDetail' => [
                'ItemRef' => [
                    'value' => $this->itemId,
                    'name' => $this->itemName,
                ],
                'ServiceDate' => $this->serviceDate,
                'Qty' => $this->quantity,
                'UnitPrice' => $this->unitPrice,
            ],
        ];
    }

    /**
     * Convert object to a Quickbooks Desktop Invoice.
     *
     * @return array
     */
    public function toDesktopArray(): array
    {
        return [
            'ServiceID' => $this->itemId,
            'ServiceName' => $this->itemName,
            'Description' => $this->description,
            'Quantity' => number_format($this->quantity, 2),
            'Rate' => number_format($this->unitPrice, 2),
            'Amount' => number_format($this->amount, 2),
        ];
    }
}