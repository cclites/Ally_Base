<?php
namespace App\Billing\Generators;

use App\Billing\Contracts\InvoiceableInterface;
use App\Business;

class BusinessInvoiceGenerator extends BaseInvoiceGenerator
{
    public function generate(Business $business) {
        /**
         * TODO:  Make sure BankAccount implements the DepositableInterface
         */
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