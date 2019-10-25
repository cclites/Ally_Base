<?php

namespace App\Reports;

use App\Billing\Queries\OnlineClientInvoiceQuery;
use Illuminate\Support\Collection;
use App\Billing\ClientInvoice;
use App\Billing\ClaimStatus;
use App\Claims\ClaimInvoice;

class InvoiceSummaryByClientTypeReport extends BaseReport
{
    /**
     * Report mode ('invoice' or 'claim').
     *
     * @var string
     */
    protected $mode;

    /**
     * Date range filter.
     *
     * @var array
     */
    protected $range = [];

    /**
     * InvoiceSummaryByClientTypeReport constructor.
     * @param OnlineClientInvoiceQuery $query
     */
    public function __construct(OnlineClientInvoiceQuery $query)
    {
        $this->query = $query->with('client', 'items', 'claimInvoice.items');
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
     * Set filters for the report.
     *
     * @param string $mode
     * @param array $dateRange
     */
    public function applyFilters(string $mode, array $dateRange): void
    {
        $this->mode = $mode;
        $this->range = $dateRange;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results(): ?iterable
    {
        $query = clone $this->query;

        $query->whereBetween('created_at', [$this->range[0], $this->range[1]]);

        if ($this->mode == 'claim') {
            $query->whereHas('claimInvoice', function ($q) {
                $q->whereIn('status', ClaimStatus::transmittedStatuses());
            });
        }

        return $query->get()
            ->map(function (ClientInvoice $invoice) {
                if ($this->mode == 'claim') {
                    /** @var ClaimInvoice $invoice */
                    $invoice = $invoice->claimInvoice;
                }

                return [
                    'client_type' => $invoice->client->client_type,
                    'hours' => $invoice->getTotalHours(),
                    'hourly_charges' => $invoice->getTotalHourlyCharges(),
                    'total_charges' => $invoice->getAmount(),
                    'item_count' => $invoice->getItems()->count(),
                ];
            })
            ->groupBy('client_type')
            ->map(function (Collection $items, string $clientType) {
                $charges = $items->sum('total_charges');
                return [
                    'client_type' => $clientType,
                    'hours' => $items->sum('hours'),
                    'hourly_charges' => $items->sum('hourly_charges'),
                    'total_charges' => $charges,
                    'average_charge' => divide($charges, $items->sum('item_count')),
                ];
            })
            ->sortBy('client_type')
            ->values();
    }
}
