<?php

namespace App\Reports;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Queries\ClientInvoiceQuery;
use Illuminate\Support\Collection;

class PaymentSummaryReport extends BaseReport
{
    protected $query;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * PaymentSummaryByPayerReport constructor.
     */
    public function __construct(ClientInvoiceQuery $query)
    {
        // TODO: this still has an n+1 issue with invoiceable shift meta
        $this->query = $query->with([
            'items',

            'items.invoiceable.meta',
            'items.shift.meta',
            'items.shift.shiftFlags',
            'items.shift.service',
            'items.shift.services.meta',

            'items.shiftService',
            'items.shiftService.service',

            'client',
            'client.user',

            'payments',
        ])->whereHas('payments');
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
     * @param array $dateRange
     * @param string|null $client_type
     * @param int|null $client
     * @param string|null $payment_method
     * @return $this
     */
    public function applyFilters(array $dateRange, ?string $client_type, ?int $client, ?string $payment_method): self
    {
        if (filled($dateRange) && count($dateRange) > 1) {
            $this->query->forDateRange($dateRange);
        }

        if (filled($client_type)) {
            $this->query->forClientType($client_type);
        }

        if (filled($client)) {
            $this->query->forClient($client, false);
        }

        if (filled($payment_method)) {
            $this->query->whereHas('payments', function ($q) use ($payment_method) {
                $q->where('payment_type', $payment_method);
            });
        }

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results(): iterable
    {
        return $this->query->get()
            ->map(function (ClientInvoice $invoice) {
                $costs = $invoice->items
                    ->whereIn('invoiceable_type', ['shifts', 'shift_services'])
                    ->map(function (ClientInvoiceItem $item) {
                        // Temporary fix: Get amounts off the shift related to the invoice
                        // related to the payment that uses this payment method.  This
                        // has many flaws and needs to be fixed with shift billing details.
                        $shift = $item->invoiceable->getShift();

                        if (empty($shift)) {
                            return [
                                'amount' => floatval(0),
                                'ally_amount' => floatval(0),
                                'caregiver_amount' => floatval(0),
                                'registry_amount' => floatval(0),
                            ];
                        }

                        return [
                            'amount' => floatval(0),
                            'ally_amount' => multiply($item->invoiceable->getItemUnits(), $item->invoiceable->getAllyRate()),
                            'caregiver_amount' => multiply($item->invoiceable->getItemUnits(), $item->invoiceable->getCaregiverRate()),
                            'registry_amount' => multiply($item->invoiceable->getItemUnits(), $item->invoiceable->getProviderRate()),
                        ];
                    });
                $costs = $this->sumAmounts($costs);

                $payment = $invoice->payments->first();

                return array_merge($costs, [
                    'client_id' => $invoice->client_id,
                    'client_name' => $invoice->client->nameLastFirst,
                    'client_type' => $invoice->client->client_type,
                    'payment_type' => optional($payment)->payment_type,
                    'date' => $invoice->getDate(),
                    'invoice_id' => $invoice->id,
                    'invoice' => $invoice->name,
                    'amount' => $invoice->amount,
                ]);
            });
    }

    public function sumAmounts(Collection $collection)
    {
        return $collection->reduce(function (array $item, array $carry) {
            $carry['ally_amount'] = add($item['ally_amount'], $carry['ally_amount']);
            $carry['caregiver_amount'] = add($item['caregiver_amount'], $carry['caregiver_amount']);
            $carry['registry_amount'] = add($item['registry_amount'], $carry['registry_amount']);
            $carry['amount'] = add($item['amount'], $carry['amount']);
            return $carry;
        }, [
            'amount' => floatval(0),
            'ally_amount' => floatval(0),
            'caregiver_amount' => floatval(0),
            'registry_amount' => floatval(0),
        ]);
    }

    public function totals()
    {
        return $this->sumAmounts(collect($this->rows()));
    }
}