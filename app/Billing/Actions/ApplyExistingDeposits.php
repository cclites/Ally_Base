<?php


namespace App\Billing\Actions;


use App\Billing\CaregiverInvoice;
use App\Billing\Queries\DepositQuery;
use App\BusinessChain;

class ApplyExistingDeposits
{
    /**
     * @var \App\Billing\Actions\ApplyDeposit
     */
    protected $depositApplicator;
    /**
     * @var \App\Billing\Queries\DepositQuery
     */
    protected $depositQuery;
    /**
     * @var \App\Billing\Actions\DepositInvoiceAggregator
     */
    protected $invoiceAggregator;

    public function __construct(ApplyDeposit $depositApplicator = null, DepositQuery $depositQuery = null, DepositInvoiceAggregator $invoiceAggregator = null)
    {
        $this->depositApplicator = $depositApplicator ?: app(ApplyDeposit::class);
        $this->depositQuery = $depositQuery ?: app(DepositQuery::class);
        $this->invoiceAggregator = $invoiceAggregator ?: app(DepositInvoiceAggregator::class);
    }

    public function toChain(BusinessChain $chain)
    {
        $invoices = $this->invoiceAggregator->dueForChain($chain);
        foreach($invoices as $invoice) {
            if ($invoice instanceof CaregiverInvoice) {
                $deposits = $this->depositQuery
                    ->forCaregiver($invoice->caregiver)
                    ->hasAmountAvailable()
                    ->get();
                foreach($deposits as $deposit) {
                    $this->depositApplicator->apply($invoice, $deposit);
                }
            }
        }
    }

}