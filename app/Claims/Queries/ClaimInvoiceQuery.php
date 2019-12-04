<?php

namespace App\Claims\Queries;

use Illuminate\Database\Eloquent\Model;
use App\Billing\Queries\BaseQuery;
use App\Claims\ClaimInvoiceType;
use App\Billing\ClaimStatus;
use App\Claims\ClaimInvoice;
use App\User;

class ClaimInvoiceQuery extends BaseQuery
{
    /**
     * @inheritDoc
     */
    function getModelInstance(): Model
    {
        return new ClaimInvoice();
    }

    /**
     * Filter claims that have a specific type.
     *
     * @param ClaimInvoiceType $type
     * @return $this
     */
    public function withType(ClaimInvoiceType $type) : self
    {
        $this->where('claim_invoice_type', '=', $type);

        return $this;
    }

    /**
     * Filter claims that have a specific status.
     *
     * @param ClaimStatus $status
     * @return $this
     */
    public function withStatus(ClaimStatus $status) : self
    {
        $this->where('status', '=', $status);

        return $this;
    }

    /**
     * Filter claims to only those for active clients.
     *
     * @return $this
     */
    public function forActiveClientsOnly() : self
    {
        $this->whereHas('clientInvoices', function ($q) {
            $q->whereHas('client', function ($q) {
                $q->active();
            });
        });

        return $this;
    }

    /**
     * Search claims by client invoice ID or name.
     *
     * @param $invoiceIdOrName
     * @return $this
     */
    public function searchForInvoiceId($invoiceIdOrName) : self
    {
        $this->whereHas('clientInvoices', function ($q) use ($invoiceIdOrName) {
            $q->where('id', $invoiceIdOrName)
                ->orWhere('name', $invoiceIdOrName);
        });

        return $this;
    }

    /**
     * Filter claims by clients that are of a specific type.
     *
     * @param string $clientType
     * @return $this
     */
    public function forClientType(string $clientType) : self
    {
        $this->whereHas('clientInvoices', function ($q) use ($clientType) {
            $q->whereHas('client', function ($q) use ($clientType) {
                $q->where('client_type', $clientType);
            });
        });

        return $this;
    }

    /**
     * Filter claims that are for a specific payer id.
     *
     * @param $payerId
     * @return $this
     */
    public function forPayer($payerId) : self
    {
        $this->where('payer_id', $payerId);

        return $this;
    }

    /**
     * Filter claims that are for a specific client id.
     *
     * @param $clientId
     * @return $this
     */
    public function forClient($clientId) : self
    {
        $this->where('client_id', $clientId);

        return $this;
    }

    /**
     * Filter claims with invoices dated between the given range.
     *
     * @param array $range
     * @return $this
     */
    public function whereInvoicedBetween(array $range) : self
    {
        if (count($range) != 2) {
            return $this;
        }

        $this->whereHas('clientInvoices', function ($q) use ($range) {
            $q->whereBetween('created_at', $range);
        });

        return $this;
    }

    /**
     * Filter claims with items dated between the given range.
     *
     * @param array $range
     * @return $this
     */
    public function whereDatesOfServiceBetween(array $range) : self
    {
        if (count($range) != 2) {
            return $this;
        }

        $this->whereHas('items', function ($q) use ($range) {
            $q->whereBetween('date', $range);
        });

        return $this;
    }

    /**
     * Filter by claims that are paid in full.
     *
     * @return $this
     */
    public function paidInFull() : self
    {
        $this->where('amount_due', '=', 0.00);

        return $this;
    }

    /**
     * Filter by claims that are not paid in full.
     *
     * @return $this
     */
    public function notPaidInFull() : self
    {
        $this->where('amount_due', '<>', 0.00);

        return $this;
    }

    /**
     * Automatically filter claims that are for the authorized business IDs
     * in the current form request.
     *
     * @param array|null $businessIds
     * @param User|null $authorizedUser
     * @return $this
     */
    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null) : self
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

            return $this;
        }

        if (count($businessIds)) {
            $this->forBusinesses($businessIds);
        }

        return $this;
    }

    /**
     * Filter claims that are for specific business ids.
     *
     * @param array $businessIds
     * @return $this
     */
    public function forBusinesses(array $businessIds): self
    {
        $this->whereIn('business_id', $businessIds);

        return $this;
    }
}