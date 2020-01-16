<?php

namespace App\Reports;

use App\Billing\ClientInvoice;
use App\Billing\Payment;
use App\Billing\Queries\PaymentQuery;
use App\Client;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Collection;

class ClientPaymentHistoryReport extends BaseReport
{
    protected $client = null;
    protected $year = null;

    /**
     * @inheritDoc
     */
    public function query()
    {
        if (empty($this->client)) {
            throw new InvalidArgumentException('Missing client reference.');
        }

        return (new PaymentQuery())
            ->with('invoices')
            ->forClient($this->client)
            ->forYear($this->year);
    }

    /**
     * @inheritDoc
     */
    protected function results() : iterable
    {
        $payments = $this->query()->get()
            ->map(function (Payment $payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->created_at->toDateTimeString(),
                    'amount' => $payment->amount,
                    'status' => $payment->success,
                    'invoices' => $payment->invoices->map(function (ClientInvoice $invoice) {
                        return [
                            'id' => $invoice->id,
                            'name' => $invoice->name,
                        ];
                    })
                ];
            })
            ->sortBy('payment_date')
            ->values();

        $total = $payments->where('status', 1)->bcsum('amount');

        return collect([
            'rows' => $payments,
            'total' => $total,
        ]);
    }

    /**
     * Set the report filters.
     *
     * @param Client $client
     * @param int $year
     * @return ClientPaymentHistoryReport
     */
    public function applyFilters(Client $client, int $year): self
    {
        $this->client = $client;
        $this->year = $year;

        return $this;
    }
}