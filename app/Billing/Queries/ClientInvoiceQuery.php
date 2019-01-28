<?php
namespace App\Billing\Queries;

use App\Billing\ClientInvoice;
use App\Billing\Payer;
use App\BusinessChain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientInvoiceQuery
 * @package App\Billing\Queries
 *
 * @method \Illuminate\Database\Eloquent\Collection|ClientInvoice[] get()
 */
class ClientInvoiceQuery extends BaseQuery
{

    function getModelInstance(): Model
    {
        return new ClientInvoice();
    }

    function forClient(int $clientId, bool $privatePayOnly = true): self
    {
        $this->where('client_id', $clientId);
        if ($privatePayOnly) {
            $this->where('payer_id', Payer::PRIVATE_PAY_ID);
        }

        return $this;
    }

    function forBusiness(int $businessId): self
    {
        $this->whereHas('client', function(Builder $q) use ($businessId) {
            $q->where('business_id', $businessId);
        });

        return $this;
    }

    function forBusinessChain(BusinessChain $businessChain): self
    {
        $this->whereHas('client', function(Builder $q) use ($businessChain) {
            $businessIds = $businessChain->businesses()->pluck('id')->toArray();
            $q->whereIn('business_id', $businessIds);
        });

        return $this;
    }

    function paidInFull(): self
    {
        $this->whereColumn('amount_paid', '==', 'amount');

        return $this;
    }

    function notPaidInFull(): self
    {
        $this->whereColumn('amount_paid', '!=', 'amount');

        return $this;
    }
}