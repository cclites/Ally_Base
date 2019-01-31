<?php
namespace App\Billing\Generators;

use App\Billing\BusinessInvoice;
use App\Billing\BusinessInvoiceItem;
use App\Billing\Contracts\InvoiceableInterface;
use App\Business;

class BusinessInvoiceGenerator extends BaseInvoiceGenerator
{
    public function generate(Business $business): ?BusinessInvoice {

        $invoiceables = $this->getInvoiceables($business);
        if (!count($invoiceables)) {
            return null;
        }

        $invoice = BusinessInvoice::create([
            'name' => BusinessInvoice::getNextName($business->id),
            'business_id' => $business->id,
        ]);

        foreach($invoiceables as $invoiceable) {
            $itemData = $this->getItemData($invoiceable);
            $item = new BusinessInvoiceItem($itemData);
            $invoice->addItem($item);
        }

        return $invoice;
    }

    /**
     * @param \App\Business $business
     * @return \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getInvoiceables(Business $business): array
    {
        $invoiceables = [];
        foreach($this->getInvoiceableClasses() as $class) {
            $invoiceables = array_merge($invoiceables, $class->getItemsForBusinessDeposit($business)->all());
        }
        return $invoiceables;
    }

    /**
     * @param \App\Billing\Contracts\InvoiceableInterface $invoiceable
     * @param float $split
     * @param float $allowance
     * @return array
     */
    public function getItemData(InvoiceableInterface $invoiceable): array
    {
        $total = multiply($invoiceable->getItemUnits(), $invoiceable->getProviderRate());

        return [
            'name' => $invoiceable->getItemName(BusinessInvoice::class),
            'group' => $invoiceable->getItemGroup(BusinessInvoice::class),
            'units' => $invoiceable->getItemUnits(),
            'client_rate' => $invoiceable->getClientRate(),
            'caregiver_rate' => $invoiceable->getCaregiverRate(),
            'ally_rate' => $invoiceable->getAllyRate(),
            'rate' => $invoiceable->getProviderRate(),
            'total' => $total,
            'date' => $invoiceable->getItemDate(),
        ];
    }
}