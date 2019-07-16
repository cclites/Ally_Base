<?php
namespace App\Billing\Actions;

use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Queries\BusinessInvoiceQuery;
use App\Billing\Queries\CaregiverInvoiceQuery;
use App\Business;
use App\BusinessChain;
use App\Caregiver;
use Illuminate\Support\Collection;

class DepositInvoiceAggregator
{

    /**
     * @var \App\Billing\Queries\CaregiverInvoiceQuery
     */
    private $_caregiverInvoiceQuery;

    /**
     * @var \App\Billing\Queries\BusinessInvoiceQuery
     */
    private $_businessInvoiceQuery;

    public function __construct(CaregiverInvoiceQuery $caregiverInvoiceQuery = null, BusinessInvoiceQuery $businessInvoiceQuery = null)
    {
        $this->_caregiverInvoiceQuery = $caregiverInvoiceQuery ?: app(CaregiverInvoiceQuery::class);
        $this->_businessInvoiceQuery = $businessInvoiceQuery ?: app(BusinessInvoiceQuery::class);
    }

    /**
     * Get all unpaid invoices for a chain
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Support\Collection|DepositInvoiceInterface[]
     */
    function dueForChain(BusinessChain $chain): Collection
    {
        $caregiverInvoices = $this->caregiverForChainQuery($chain)->get();
        $businessInvoices = $this->businessForChainQuery($chain)->get();
        return $caregiverInvoices->merge($businessInvoices);
    }

    /**
     * Get all unpaid invoices for a caregiver
     *
     * @param Caregiver $caregiver
     * @return Collection|CaregiverInvoice[]
     */
    function dueForCaregiver(Caregiver $caregiver): Collection
    {
        return $this->caregiverInvoiceQuery()->forCaregiver($caregiver->id)->get();
    }

    /**
     * Get all unpaid invoices for a business
     *
     * @param Business $business
     * @return Collection|BusinessInvoice[]
     */
    function dueForBusiness(Business $business): Collection
    {
        return $this->businessInvoiceQuery()->forBusiness($business->id)->get();
    }

    /**
     * Get all caregivers with eligible invoices for a chain
     *
     * @param BusinessChain $chain
     * @return Collection|Caregiver[]
     */
    function getEligibleCaregivers(BusinessChain $chain): Collection
    {
        $ids = $this->caregiverForChainQuery($chain)->pluck('caregiver_id');
        return Caregiver::whereIn('id', $ids)->get();
    }

    /**
     * Get all businesses with eligible invoices for a chain
     *
     * @param BusinessChain $chain
     * @return Collection|Business[]
     */
    function getEligibleBusinesses(BusinessChain $chain): Collection
    {
        $ids = $this->businessForChainQuery($chain)->pluck('business_id');
        return Business::whereIn('id', $ids)->get();
    }

    private function caregiverInvoiceQuery(): CaregiverInvoiceQuery
    {
        // Always sort by the amount so that negative balances can
        // be applied first and we can calculate the proper balance
        // to apply to the positive invoices.
        return $this->_caregiverInvoiceQuery
            ->notPaidInFull()
            ->notOnHold()
            ->orderBy('amount');
    }

    private function businessInvoiceQuery(): BusinessInvoiceQuery
    {
        // Always sort by the amount so that negative balances can
        // be applied first and we can calculate the proper balance
        // to apply to the positive invoices.
        return $this->_businessInvoiceQuery
            ->notPaidInFull()
            ->notOnHold()
            ->orderBy('amount');
    }

    private function caregiverForChainQuery(BusinessChain $chain): CaregiverInvoiceQuery
    {
        return $this->caregiverInvoiceQuery()
            ->forBusinessChain($chain);
    }

    private function businessForChainQuery(BusinessChain $chain): BusinessInvoiceQuery
    {
        return $this->businessInvoiceQuery()
            ->forBusinessChain($chain);
    }


}