<?php
namespace App\Billing\Generators;

use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\Contracts\InvoiceableInterface;
use App\Caregiver;

class CaregiverInvoiceGenerator extends BaseInvoiceGenerator
{
    public function generate(Caregiver $caregiver): ?CaregiverInvoice {

        $invoiceables = $this->getInvoiceables($caregiver);
        if (!count($invoiceables)) {
            return null;
        }

        $invoice = CaregiverInvoice::create([
            'name' => CaregiverInvoice::getNextName($caregiver->id),
            'caregiver_id' => $caregiver->id,
        ]);

        foreach($invoiceables as $invoiceable) {
            $itemData = $this->getItemData($invoiceable);
            $item = new CaregiverInvoiceItem($itemData);
            $item->associateInvoiceable($invoiceable);
            $invoice->addItem($item);
        }

        return $invoice;
    }

    /**
     * @param \App\Caregiver $caregiver
     * @return \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getInvoiceables(Caregiver $caregiver): array
    {
        $invoiceables = [];
        foreach($this->getInvoiceableClasses() as $class) {
            $invoiceables = array_merge($invoiceables, $class->getItemsForCaregiverDeposit($caregiver)->all());
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
        $total = multiply($invoiceable->getItemUnits(), $invoiceable->getCaregiverRate());

        return [
            'name' => $invoiceable->getItemName(CaregiverInvoice::class),
            'group' => $invoiceable->getItemGroup(CaregiverInvoice::class),
            'units' => $invoiceable->getItemUnits(),
            'rate' => $invoiceable->getCaregiverRate(),
            'total' => $total,
            'date' => $invoiceable->getItemDate(),
        ];
    }
}