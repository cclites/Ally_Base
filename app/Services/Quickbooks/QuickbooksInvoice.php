<?php
namespace App\Services\Quickbooks;

class QuickbooksInvoice
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
     * @var \Illuminate\Support\Collection|QuickbooksInvoiceItem[]
     */
    public $lineItems = [];

    /**
     * @var \Carbon\Carbon
     */
    public $date;

    public function addItem(QuickbooksInvoiceItem $item)
    {
        $this->lineItems[] = $item;
    }

    public function toArray()
    {
        $items = [];
        foreach ($this->lineItems as $item) {
            $items[] = $item->toArray();
        }

        return [
//            'DocNumber' => '101',
            'TxnDate' => $this->date->format('Y-m-d'),
            'Line' => $items,
            'CustomerRef' => [
                'value' => $this->customerId,
                'name' => $this->customerName
            ]
        ];
    }
}