<?php

namespace App\Reports;

use App\Billing\Queries\OfflineClientInvoiceQuery;
use Carbon\Carbon;

class BusinessOfflineArAgingReport extends BaseReport
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
     * @var array
     */
    protected $datePeriods = [];

    /**
     * BusinessOfflineArAgingReport constructor.
     * @param OfflineClientInvoiceQuery $query
     */
    public function __construct(OfflineClientInvoiceQuery $query)
    {
        $this->query = $query->with('client')
            ->notPaidInFull();

        $today = \Carbon\Carbon::now();
        $this->datePeriods = [
            ['period' => 'current', 'start' => $today->copy()->subDays(30), 'end' => $today->copy()],
            ['period' => '30_45', 'start' => $today->copy()->subDays(45), 'end' => $today->copy()->subDays(30)],
            ['period' => '46_60', 'start' => $today->copy()->subDays(60), 'end' => $today->copy()->subDays(46)],
            ['period' => '61_75', 'start' => $today->copy()->subDays(75), 'end' => $today->copy()->subDays(61)],
            ['period' => '75_plus', 'start' => Carbon::parse('01/01/2018'), 'end' => $today->copy()->subDays(75)],
        ];
    }

    /**
     * Add Client filter.
     *
     * @param null|int $clientId
     * @return self
     */
    public function forClient(?int $clientId) : self
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
    public function forPayer(?int $payerId) : self
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
    protected function results() : ?iterable
    {
        $query = clone $this->query;

        if (filled($this->clientId)) {
            $query->where('client_id', $this->clientId);
        }

        if (filled($this->payerId)) {
            $query->whereHas('clientPayer', function ($q) {
                $q->where('payer_id', $this->payerId);
            });
        }

        $clients = $query->get()->pluck('client');

        $periodData = [];
        foreach ($this->datePeriods as $period) {
            $periodData[$period['period']] = $this->getBalanceForDates($clients->pluck('id'), [$period['start'], $period['end']]);
        }

        $report = collect([]);
        foreach ($clients as $client) {
            $record = [
                'client_name' => $client->name,
            ];

            foreach ($this->datePeriods as $period) {
                $record[$period['period']] = (float) $periodData[$period['period']]->where('client_id', $client->id)->sum('balance');
            }

            $report->push($record);
        }

        return $report;
        return $query->get()->map(function ($item) {
            return array_merge($item->toArray(), [
                'client_name' => $item->client->name,
            ]);
        });
    }

    /**
     * Get the balance for a customer and their services addresses
     * between the given date period.
     *
     * @param array|\Illuminate\Support\Collection $clientIds
     * @param array $dates
     * @return mixed
     */
    public function getBalanceForDates(iterable $clientIds, array $dates) : ?iterable
    {
        return $this->query
            ->select('client_id', \DB::raw("SUM(amount - offline_amount_paid) as balance"))
            ->whereIn('client_id', $clientIds)
            ->whereBetween('created_at', $dates)
            ->groupBy('client_id')
            ->get();
	}
}
