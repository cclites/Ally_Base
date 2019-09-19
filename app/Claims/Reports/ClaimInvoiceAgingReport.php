<?php

namespace App\Claims\Reports;

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
     * BusinessOfflineArAgingReport constructor.
     */
    public function __construct()
    {
        $this->query = ClaimInvoice::query()
            ->with('client', 'payer', 'business', 'clientInvoice')
            ->where('amount_due', '<>', '0')
            ->whereIn('status', ClaimStatus::transmittedStatuses());
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
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
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

        if (filled($this->clientId)) {
            $query->where('client_id', $this->clientId);
        }

        if (filled($this->payerId)) {
            $query->where('payer_id', $this->payerId);
        }

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
