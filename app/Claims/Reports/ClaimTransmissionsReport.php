<?php

namespace App\Claims\Reports;

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

    protected $totalAmount = 0.00;
    protected $totalDue = 0.00;

    /**
     * BusinessOfflineArAgingReport constructor.
     */
    public function __construct()
    {
        $this->query = ClaimInvoice::query()
            ->with('payer', 'business', 'clientInvoice')
            ->whereNotNull('transmitted_at');
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

        if (filled($this->range)) {
            $query->whereBetween('transmitted_at', $this->range);
        }

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
