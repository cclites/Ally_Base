<?php
namespace App\Billing\Queries;

use App\Billing\CaregiverInvoice;
use App\BusinessChain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CaregiverInvoiceQuery
 * @package App\Billing\Queries
 *
 * @method \Illuminate\Database\Eloquent\Collection|CaregiverInvoice[] get()
 */
class CaregiverInvoiceQuery extends BaseQuery
{

    function getModelInstance(): Model
    {
        return new CaregiverInvoice();
    }

    function forCaregiver(int $caregiverId): self
    {
        $this->where('caregiver_id', $caregiverId);

        return $this;
    }

    function forBusinessChain(BusinessChain $businessChain): self
    {
        $this->whereHas('caregiver', function(Builder $q) use ($businessChain) {
            $q->whereHas('businessChains', function($q) use ($businessChain) {
                $q->where('chain_id', $businessChain->id);
            });
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
        $this->whereDoesntHave('caregiver', function($q) {
            $q->has('paymentHold');
        });

        return $this;
    }
}