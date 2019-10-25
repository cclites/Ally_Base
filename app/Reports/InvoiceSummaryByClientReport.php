<?php

namespace App\Reports;

use App\Billing\Queries\OnlineClientInvoiceQuery;
use Illuminate\Support\Collection;
use App\Billing\ClientInvoice;
use App\Billing\ClaimStatus;
use App\Claims\ClaimInvoice;

class InvoiceSummaryByClientReport extends BaseReport
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
     * Client Type filter.
     *
     * @var string
     */
    protected $clientType;

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
     * InvoiceSummaryByClientReport constructor.
     * @param OnlineClientInvoiceQuery $query
     */
    public function __construct(OnlineClientInvoiceQuery $query)
    {
        $this->query = $query->with('client', 'clientPayer.payer', 'items', 'claimInvoice.items');
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
     * @param string $clientType
     * @param string $payerId
     * @param string $clientId
     */
    public function applyFilters(string $mode, array $dateRange, ?string $clientType, ?string $payerId, ?string $clientId): void
    {
        $this->mode = $mode;
        $this->range = $dateRange;
        $this->clientType = $clientType;
        $this->clientId = $clientId === null ? null : (int)$clientId;
        $this->payerId = $payerId === null ? null : (int)$payerId;
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

        if (filled($this->clientId)) {
            $query->forClient($this->clientId);
        }

        if (filled($this->clientType)) {
            $query->forClientType($this->clientType);
        }

        if (filled($this->payerId)) {
            $query->forPayer($this->payerId);
        }

        return $query->get()
            ->map(function (ClientInvoice $invoice) {
                $totalsObj = $this->mode == 'claim' ? $invoice->claimInvoice : $invoice;

                return [
                    'invoice_id' => $invoice->id,
                    'invoice_name' => $invoice->name,
                    'invoice_date' => $invoice->getDate(),
                    'claim_id' => optional($invoice->claimInvoice)->id,
                    'claim_name' => optional($invoice->claimInvoice)->name,
                    'client_type' => $invoice->client->client_type,
                    'client_id' => $invoice->client_id,
                    'client_name' => $invoice->client->nameLastFirst,
                    'payer_name' => optional(optional($invoice->clientPayer)->payer)->name,
                    'hours' => $totalsObj->getTotalHours(),
                    'hourly_charges' => $totalsObj->getTotalHourlyCharges(),
                    'total_charges' => $totalsObj->getAmount(),
                    'date_range' => '-', // TODO
                ];
            })
            ->groupBy('client_id')
            ->map(function (Collection $invoices) {
                $first = $invoices->first();
                return [
                    'client_id' => optional($first)['client_id'],
                    'client_name' => optional($first)['client_name'],
                    'invoice_count' => $invoices->count(),

                    'hours' => $invoices->sum('hours'),
                    'hourly_charges' => $invoices->sum('hourly_charges'),
                    'total_charges' => $invoices->sum('total_charges'),
                    'invoices' => $invoices,
                ];
            })
            ->sortBy('client_name')
            ->values();
    }

    /**
     * Download the report to an xlsx file.
     */
    public function download()
    {
        $this->rows = $this->results()->map(function ($row) {
            return collect($row['invoices']->toArray(request()))->map(function ($item) {
                return [
                    'client_name' => $item['client_name'],
                    'invoice_name' => $item['invoice_name'],
                    'invoice_date' => $item['invoice_date'],
                    'payer_name' => $item['payer_name'],
                    'date_range' => $item['date_range'],
                    'hours' => $item['hours'],
                    'hourly_charges' => $item['hourly_charges'],
                    'total_charges' => $item['total_charges'],
                ];
            });
        })->flatten(1);

        parent::download();
    }
}
