<?php
namespace App\Billing\Queries;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Payer;
use App\BusinessChain;
use App\User;
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
            $this->whereHas('clientPayer', function($q) {
                $q->where('payer_id', Payer::PRIVATE_PAY_ID);
            });
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

    function forBusinesses(array $businessIds): self
    {
        $this->whereHas('client', function(Builder $q) use ($businessIds) {
            $q->whereIn('business_id', $businessIds);
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

    function excludesOffline(): self
    {
        $this->where('offline', false);

        return $this;
    }

    function paidInFull(): self
    {
        $this->whereColumn('amount_paid', '=', 'amount')
            ->excludesOffline();

        return $this;
    }

    function notPaidInFull(): self
    {
        $this->whereColumn('amount_paid', '!=', 'amount')
            ->excludesOffline();

        return $this;
    }

    function notOnHold(): self
    {
        $this->whereDoesntHave('client', function($q) {
            $q->has('paymentHold');
        });

        return $this;
    }

    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null)
    {
        if ($businessIds === null) $businessIds = array_filter((array) request()->input('businesses', []));
        if ($authorizedUser === null) $authorizedUser = auth()->user();

        if ($authorizedUser->role_type !== 'admin') {
            $businessIds = $authorizedUser->filterAttachedBusinesses($businessIds);
            // If empty, filter by all businesses the authorized user has access to
            if (!count($businessIds)) $businessIds = $authorizedUser->getBusinessIds();

            $this->forBusinesses($businessIds);
            return;
        }

        if (count($businessIds)) {
            $this->forBusinesses($businessIds);
        }
    }
}