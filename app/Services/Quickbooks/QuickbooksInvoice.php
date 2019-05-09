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

    /**
     * @var string
     */
    public $invoiceId;

    /**
     * Add a line item.
     *
     * @param QuickbooksInvoiceItem $item
     */
    public function addItem(QuickbooksInvoiceItem $item) : void
    {
        $this->lineItems[] = $item;
    }

    /**
     * Convert the object to a Quickbooks API Invoice.
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
            'Line' => $items,
            'CustomerRef' => [
                'value' => $this->customerId,
                'name' => $this->customerName
            ]
        ];
    }
}