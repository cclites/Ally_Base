<?php

namespace App\Claims\Reports;

use App\Claims\Queries\ClaimInvoiceQuery;
use App\Claims\ClaimStatus;
use App\Claims\ClaimInvoice;
use App\Reports\BaseReport;

class ClaimTransmissionsReport extends BaseReport
{
    /**
     * Client filter.
     *
     * @var int
     */
    protected $clientId;

    /**
     * Date range filter.
     *
     * @var array
     */
    protected $range = [];

    /**
     * ClientType filter
     *
     * @var string
     */
    protected $clientType;

    /**
     * @var bool
     */
    protected $showInactive = false;

    /**
     * ClaimTransmissionsReport constructor.
     */
    public function __construct()
    {
        $this->query = (new ClaimInvoiceQuery())
            ->with('payer')
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
     * @param array $range
     * @return self
     */
    public function forDateRange(array $range): self
    {
        $this->range = $range;

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
            ->when(filled($this->range), function (ClaimInvoiceQuery $q) {
                $q->whereTransmittedBetween($this->range);
            })
            ->when($this->clientType, function (ClaimInvoiceQuery $q) {
                $q->forClientType($this->clientType);
            })
            ->when(! $this->showInactive, function (ClaimInvoiceQuery $q) {
                $q->forActiveClientsOnly();
            });

        return $query->get()->groupBy('payer_id')
            ->map(function ($payerGroup) {
                $payer = $payerGroup->first()->payer;
                $amount = $payerGroup->reduce(function (float $carry, ClaimInvoice $claim) {
                        return add($carry, floatval($claim->amount));
                    }, floatval(0.00));
                $due = $payerGroup->reduce(function (float $carry, ClaimInvoice $claim) {
                        return add($carry, floatval($claim->amount_due));
                    }, floatval(0.00));

                return [
                    'payer_id' => $payer->id,
                    'payer_name' => $payer->name,
                    'amount' => $amount,
                    'amount_due' => $due,
                ];
            })->values();
    }

    /**
     * Get the report totals.
     *
     * @return array
     */
    public function getTotals() : array
    {
        $results = $this->rows();

        $totalAmount = $results->reduce(function (float $carry, array $row) {
            return add($carry, floatval($row['amount']));
        }, floatval(0.00));

        $totalDue = $results->reduce(function (float $carry, array $row) {
            return add($carry, floatval($row['amount']));
        }, floatval(0.00));

        return [
            'total_amount' => $totalAmount,
            'total_due' => $totalDue,
        ];
    }
}
