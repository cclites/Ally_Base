<?php

namespace App\Reports;

use App\Billing\Queries\OnlineClientInvoiceQuery;
use Illuminate\Support\Collection;
use App\Billing\ClientInvoice;
use App\Claims\ClaimStatus;
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
        $this->query = $query->with('client', 'items', 'claimInvoices.items');
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

        $query->forDateRange($this->range);

        if ($this->mode == 'claim') {
            $query->hasClaim(true);
        }

        return $query->get()
            ->map(function (ClientInvoice $invoice) {
                /** @var ClaimInvoice $claimInvoice */
                $claimInvoice = $invoice->claimInvoices->first();
                $totalsObj = $this->mode == 'claim' ? $claimInvoice : $invoice;

                return [
                    'client_type' => $invoice->client->client_type,
                    'item_count' => $invoice->getItems()->count(),

                    'hours' => $totalsObj->getTotalHours($invoice->id),
                    'hourly_charges' => $totalsObj->getTotalHourlyCharges($invoice->id),
                    'total_charges' => ($this->mode == 'claim')?  $claimInvoice->getAmountForInvoice($invoice->id) : $totalsObj->getAmount(),
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
