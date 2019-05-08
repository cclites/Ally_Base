<?php
namespace App\Services\Quickbooks;

class QuickbooksInvoiceItem
{
    /**
     * @var string
     */
    public $description;
    public $amount;
    public $itemId;
    public $itemName;

    public function toArray()
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
            ],
        ];
    }
}