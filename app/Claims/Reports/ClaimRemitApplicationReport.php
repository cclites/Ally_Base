<?php

namespace App\Claims\Reports;

use App\Claims\Resources\ClaimRemitResource;
use App\Reports\BaseReport;
use App\Claims\ClaimRemit;

class ClaimRemitApplicationReport extends BaseReport
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
     * Payer ID filter.
     *
     * @var string
     */
    protected $payerId;

    /**
     * Payment Type filter.
     *
     * @var string
     */
    protected $paymentType;

    /**
     * BusinessOfflineArAgingReport constructor.
     */
    public function __construct()
    {
        $this->query = ClaimRemit::with('business');
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

        if (filled($this->range) && count($this->range) > 1) {
            $query->forDateRange($this->range[0], $this->range[1]);
        }

        if (filled($this->payerId)) {
            $query->forPayer($this->payerId);
        }

        if (filled($this->paymentType)) {
            $query->withType($this->paymentType);
        }

        return $query->get()->groupBy('payer_id')
            ->map(function ($payerGroup) {
                $payer = $payerGroup->first()->payer;

                $amount = $payerGroup->reduce(function (float $carry, ClaimRemit $remit) {
                    return add($carry, floatval($remit->amount));
                }, floatval(0.00));

                $available = $payerGroup->reduce(function (float $carry, ClaimRemit $remit) {
                    return add($carry, floatval($remit->getAmountAvailable()));
                }, floatval(0.00));

                $applied = $payerGroup->reduce(function (float $carry, ClaimRemit $remit) {
                    return add($carry, floatval($remit->amount_applied));
                }, floatval(0.00));

                return [
                    'payer_id' => optional($payer)->id,
                    'payer' => empty($payer) ? '(No Payer)' : $payer->name,
                    'total_payments' => $payerGroup->count(),
                    'total_amount' => $amount,
                    'total_amount_available' => $available,
                    'remits' => ClaimRemitResource::collection($payerGroup),
                ];
            })
            ->sortBy('payer')
            ->values();
    }

    /**
     * Set filters for the report.
     *
     * @param string|null $payerId
     * @param array $dateRange
     * @param string|null $paymentType
     */
    public function applyFilters(?string $payerId, array $dateRange, ?string $paymentType): void
    {
        $this->payerId = $payerId;
        $this->range = $dateRange;
        $this->paymentType = $paymentType;
    }

    /**
     * Download the report to an xlsx file.
     */
    public function download()
    {
        $this->rows = $this->results()->map(function ($row) {
            return collect($row['remits']->toArray(request()))->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'office_location' => $item['office_location'],
                    'payment_date' => $item['date'],
                    'payment_type' => $item['payment_type'],
                    'payer' => $item['payer_name'],
                    'reference' => $item['reference'],
                    'amount' => $item['amount'],
                    'amount_applied' => $item['amount_applied'],
                    'amount_available' => $item['amount_available'],
                    'status' => $item['status'],
                    'date_added' => $item['created_at'],
                    'notes' => $item['notes'],
                ];
            });
        })->flatten(1);

        parent::download();
    }
}
