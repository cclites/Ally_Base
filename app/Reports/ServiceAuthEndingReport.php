<?php

namespace App\Reports;

use App\Billing\ClientAuthorization;
use Carbon\Carbon;

class ServiceAuthEndingReport extends BaseReport
{
    /**
     * @var bool
     */
    private $isPast = false;

    /**
     * ServiceAuthEndingReport constructor.
     */
    public function __construct()
    {
        $this->query = ClientAuthorization::with('client', 'client.user', 'service');
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
        return $this->query
            ->get()
            ->groupBy('client_id')
            ->map(function ($items) {
                return [
                    'id' => $items[0]->client->id,
                    'name' => $items[0]->client->nameLastFirst,
                    'total' => count($items),
                    'authorizations' => $items->map(function (ClientAuthorization $auth) {
                        return array_merge($auth->toArray(), [
                            'days_until_end' => ($this->isPast ? '-' : '') . Carbon::today()->diffInDays(Carbon::parse($auth->effective_end)),
                        ]);
                    }),
                ];
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * Apply filters to the query.
     *
     * @param iterable $clientIds
     * @param string|null $days
     * @param string $timezone
     * @return ServiceAuthEndingReport
     */
    public function applyFilters(iterable $clientIds, ?string $days, string $timezone) : self
    {
        $this->query->whereHas('client', function($q) use ($clientIds) {
            $q->active()->whereIn('id', $clientIds);
        });

        if ($days === null || !is_numeric($days)) {
            $days = 30;
        }

        $days = (int) $days;

        $today = Carbon::today();
        if ($days < 0) {
            $this->isPast = true;
            $range = [$today->copy()->addDays($days)->format('Y-m-d'), $today->copy()->format('Y-m-d')];
        } else {
            $range = [$today->copy()->format('Y-m-d'), $today->copy()->addDays($days)->format('Y-m-d')];
        }
        $this->query->whereBetween('effective_end', $range);

        return $this;
    }
}
