<?php
namespace App\Billing\Generators;

use App\Billing\Contracts\Invoiceable;
use App\Business;

class BusinessInvoiceGenerator extends BaseInvoiceGenerator
{
    public function generate(Business $business) {

    }

    /**
     * @param \App\Business $business
     * @return \App\Billing\Contracts\Invoiceable[]
     */
    public function getInvoiceables(Business $business): array
    {
        $invoiceables = [];
        foreach($this->getInvoiceableClasses() as $class) {
            $invoiceables[] = $class->getItemsForBusinessDeposit($business);
        }
        return $invoiceables;
    }

    /**
     * @param \App\Billing\Contracts\Invoiceable $invoiceable
     * @param float $split
     * @param float $allowance
     * @return array
     */
    public function getItemData(Invoiceable $invoiceable): array
    {
        $total = round(bcmul($invoiceable->getItemUnits(), $invoiceable->getClientRate(), 4), 2);

        return [
            'name' => $invoiceable->getItemName(),
            'group' => $invoiceable->getItemGroup(),
            'units' => $invoiceable->getItemUnits(),
            'rate' => $invoiceable->getProviderRate(),
            'date' => $invoiceable->getItemDate(),
            'total' => $total,
            'amount_due' => $total,
        ];
    }
}