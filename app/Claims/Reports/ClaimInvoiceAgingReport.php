<?php

namespace App\Claims\Reports;

use App\Claims\Queries\ClaimInvoiceQuery;
use App\Claims\Resources\ClaimAgingReportItemResource;
use App\Claims\ClaimInvoice;
use App\Billing\ClaimStatus;
use App\Reports\BaseReport;

class ClaimInvoiceAgingReport extends BaseReport
{
    /**
     * Client filter.
     *
     * @var int
     */
    protected $clientId;

    /**
     * Payer filter.
     *
     * @var int
     */
    protected $payerId;

    /**
     * ClientType filter
     *
     * @var string
     */
    protected $clientType;

    /**
     * Client Invoice ID/name filter.
     *
     * @var string
     */
    protected $clientInvoiceId;

    /**
     * @var bool
     */
    protected $showInactive = false;

    /**
     * ClaimInvoiceAgingReport constructor.
     */
    public function __construct()
    {
        $this->query = (new ClaimInvoiceQuery)
            ->with(['client', 'payer', 'business', 'clientInvoices', 'adjustments'])
            ->notPaidInFull()
            ->withStatus(ClaimStatus::transmittedStatuses());
    }

    /**
     * Add Client filter.
     *
     * @param null|int $clientId
     * @return self
     */
    public function forClient(?int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Add Payer filter.
     *
     * @param null|int $payerId
     * @return self
     */
    public function forPayer(?int $payerId): self
    {
        $this->payerId = $payerId;

        return $this;
    }

    /**
     * Query by client type.
     *
     * @param string $clientType
     * @return $this
     */
    public function forClientType(?string $clientType): self
    {
        $this->clientType = $clientType;

        return $this;
    }

    /**
     * Query inactive clients.
     *
     * @param bool $inactive
     * @return $this
     */
    public function showInactive(bool $inactive): self
    {
        $this->showInactive = $inactive;

        return $this;
    }

    /**
     * Query a given Client Invoice.
     *
     * @param null|string $invoiceIdOrName
     * @return $this
     */
    public function forClientInvoiceId(?string $invoiceIdOrName): self
    {
        $this->clientInvoiceId = $invoiceIdOrName;

        return $this;
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return ClaimInvoiceQuery
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results(): ?iterable
    {
        $query = clone $this->query;

        $query->when($this->clientId, function (ClaimInvoiceQuery $q) {
                $q->forClient($this->clientId);
            })
            ->when(filled($this->payerId), function (ClaimInvoiceQuery $q) {
                $q->forPayer($this->payerId);
            })
            ->when($this->clientType, function (ClaimInvoiceQuery $q) {
                $q->forClientType($this->clientType);
            })
            ->when(! $this->showInactive, function (ClaimInvoiceQuery $q) {
                $q->forActiveClientsOnly();
            })
            ->when($this->clientInvoiceId, function (ClaimInvoiceQuery $q) {
                $q->searchForInvoiceId($this->clientInvoiceId);
            });

        return $query->get()->map(function (ClaimInvoice $claim) {
            return (new ClaimAgingReportItemResource($claim))->toArray(request());
        });
    }

    /**
     * Get totals row for all periods.
     *
     * @return mixed
     */
    public function totals()
    {
        $periods = collect(['current', 'period_30_45', 'period_46_60', 'period_61_75', 'period_75_plus']);

        return $periods->mapWithKeys(function ($period) {
            $amount = $this->rows()
                ->reduce(function (float $carry, $row) use ($period) {
                    return add($carry, isset($row[$period]) ? floatval($row[$period]) : floatval(0));
                }, floatval(0));
            return [$period => $amount];
        })
            ->toArray();
    }
}
