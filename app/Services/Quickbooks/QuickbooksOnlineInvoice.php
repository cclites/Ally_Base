<?php
namespace App\Services\Quickbooks;

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
}