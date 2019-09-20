<?php


namespace App\Billing\Queries;


use App\Billing\ClientInvoice;
use App\Billing\Payer;
use App\BusinessChain;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method \Illuminate\Database\Eloquent\Collection|ClientInvoice[] get()
 */
class ClientInvoiceQuery extends BaseQuery
{

    function getModelInstance(): Model
    {
        return new ClientInvoice();
    }

    function forBusiness(int $businessId): self
    {
        $this->whereHas('client', function (Builder $q) use ($businessId) {
            $q->where('business_id', $businessId);
        });

        return $this;
    }

    function forBusinesses(array $businessIds): self
    {
        $this->whereHas('client', function (Builder $q) use ($businessIds) {
            $q->whereIn('business_id', $businessIds);
        });

        return $this;
    }

    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null)
    {
        if ($businessIds === null) {
            $businessIds = array_filter((array)request()->input('businesses', []));
        }
        if ($authorizedUser === null) {
            $authorizedUser = auth()->user();
        }

        if ($authorizedUser->role_type !== 'admin') {
            $businessIds = $authorizedUser->filterAttachedBusinesses($businessIds);
            // If empty, filter by all businesses the authorized user has access to
            if (!count($businessIds)) {
                $businessIds = $authorizedUser->getBusinessIds();
            }

            $this->forBusinesses($businessIds);
            return;
        }

        if (count($businessIds)) {
            $this->forBusinesses($businessIds);
        }
    }

    function forClient(int $clientId, bool $privatePayOnly = true): self
    {
        $this->where('client_id', $clientId);
        if ($privatePayOnly) {
            $this->whereHas('clientPayer', function ($q) {
                $q->where('payer_id', Payer::PRIVATE_PAY_ID);
            });
        }

        return $this;
    }

    function forBusinessChain(BusinessChain $businessChain): self
    {
        $this->whereHas('client', function (Builder $q) use ($businessChain) {
            $businessIds = $businessChain->businesses()->pluck('id')->toArray();
            $q->whereIn('business_id', $businessIds);
        });

        return $this;
    }

    public function forClientType(string $clientType): self
    {
        $this->whereHas('client', function (Builder $q) use ($clientType) {
            $q->where('client_type', $clientType);
        });

        return $this;
    }

    function forPayer(int $payerId) : self
    {
        $this->whereHas('clientPayer', function ($q) use ($payerId) {
            $q->where('payer_id', $payerId);
        });

        return $this;
    }

    function onlineOnly(): self
    {
        $this->where('offline', false);

        return $this;
    }

    function offlineOnly(): self
    {
        $this->where('offline', true);

        return $this;
    }
}