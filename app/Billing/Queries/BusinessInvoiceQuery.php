<?php
namespace App\Billing\Queries;

use App\Billing\BusinessInvoice;
use App\BusinessChain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BusinessInvoiceQuery
 * @package App\Billing\Queries
 *
 * @method \Illuminate\Database\Eloquent\Collection|\App\Billing\BusinessInvoice[] get()
 */
class BusinessInvoiceQuery extends BaseQuery
{

    function getModelInstance(): Model
    {
        return new BusinessInvoice();
    }

    function forBusiness(int $businessId): self
    {
        $this->where('business_id', $businessId);

        return $this;
    }

    function forBusinessChain(BusinessChain $businessChain): self
    {
        $this->whereHas('business', function(Builder $q) use ($businessChain) {
            $q->where('chain_id', $businessChain->id);
        });

        return $this;
    }

    function paidInFull(): self
    {
        $this->whereColumn('amount_paid', '=', 'amount');

        return $this;
    }

    function notPaidInFull(): self
    {
        $this->whereColumn('amount_paid', '!=', 'amount');

        return $this;
    }

    function notOnHold(): self
    {
        $this->whereDoesntHave('business', function($q) {
            $q->has('paymentHold');
        });

        return $this;
    }
}