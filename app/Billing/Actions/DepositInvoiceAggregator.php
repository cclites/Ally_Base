<?php
namespace App\Billing\Actions;

use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Queries\BusinessInvoiceQuery;
use App\Billing\Queries\CaregiverInvoiceQuery;
use App\BusinessChain;
use Illuminate\Support\Collection;

class DepositInvoiceAggregator
{

    /**
     * @var \App\Billing\Queries\CaregiverInvoiceQuery
     */
    protected $caregiverInvoiceQuery;

    /**
     * @var \App\Billing\Queries\BusinessInvoiceQuery
     */
    protected $businessInvoiceQuery;

    public function __construct(CaregiverInvoiceQuery $caregiverInvoiceQuery = null, BusinessInvoiceQuery $businessInvoiceQuery = null)
    {
        $this->caregiverInvoiceQuery = $caregiverInvoiceQuery ?: app(CaregiverInvoiceQuery::class);
        $this->businessInvoiceQuery = $businessInvoiceQuery ?: app(BusinessInvoiceQuery::class);
    }

    /**
     * Get all unpaid invoices for a chain
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Support\Collection|DepositInvoiceInterface[]
     */
    function dueForChain(BusinessChain $chain): Collection
    {
        $caregiverInvoices = $this->caregiverInvoiceQuery
            ->forBusinessChain($chain)
            ->notPaidInFull()
            ->notOnHold()
            ->get();
        $businessInvoices = $this->businessInvoiceQuery
            ->forBusinessChain($chain)
            ->notPaidInFull()
            ->notOnHold()
            ->get();
        return $caregiverInvoices->merge($businessInvoices);
    }

}