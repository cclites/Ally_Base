<?php

namespace App\Reports;

use App\Billing\ScheduleService;
use App\Schedule;
use App\Shift;
use App\Shifts\Data\ClockData;
use App\Shifts\ShiftFactory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectedBillingReport extends BaseReport
{
    /**
     * ServiceAuthEndingReport constructor.
     */
    public function __construct()
    {
        $this->query = Schedule::with('client', 'caregiver');
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
     * @return Collection
     */
    protected function results() : ?iterable
    {
        $schedules = $this->query->get();
        $stats = $this->getClientStats($schedules);

        return collect([
            'totals' => $this->getTotals($stats),
            'client_type_totals' => $this->getClientTypeTotals($stats),
            'clients' => $stats,
        ]);
    }

    /**
     * Get totals per client.
     *
     * @param Collection $schedules
     * @return Collection
     */
    protected function getClientStats(Collection $schedules): Collection
    {
        return $schedules->groupBy('client_id')
            ->map(function ($items) {
                return [
                    'id' => $items[0]->client_id,
                    'name' => $items[0]->client->nameLastFirst,
                    'client_type' => $items[0]->client->client_type,
                    'projected_billing' => $items->reduce(function ($carry, Schedule $schedule) {
                        return add((float) $carry, $this->getProjectedCost($schedule));
                    }, (float) 0.00),
                    'hours' => divide((float) $items->sum('duration'), (float) 60.0),
                ];
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * Sum up client totals and group by client type.
     *
     * @param Collection $stats
     * @return Collection
     */
    protected function getClientTypeTotals(Collection $stats): Collection
    {
        return $stats->groupBy('client_type')->map(function ($items) {
            return [
                'name' => $items[0]['client_type'],
                'projected_billing' => $items->reduce(function ($carry, $item) {
                    return add($carry, (float)$item['projected_billing']);
                }, (float)0.00),
            ];
        })
            ->sortBy('name')
            ->values();
    }

    /**
     * Sum up all Client totals.
     *
     * @param Collection $stats
     * @return array
     */
    protected function getTotals(Collection $stats): array
    {
        return [
            'total_clients' => $stats->count(),
            'total_hours' => $stats->reduce(function ($carry, $item) {
                return add($carry, (float) $item['hours']);
            }, (float) 0.0),
            'projected_total' => $stats->reduce(function ($carry, $item) {
                return add($carry, (float) $item['projected_billing']);
            }, (float) 0.00),
        ];
    }

    /**
     * Get the projected cost of a Schedule, including service breakouts
     * and fixed rate shifts.
     *
     * @param Schedule $schedule
     * @return float
     */
    protected function getProjectedCost(Schedule $schedule) : float
    {
        $clockIn = new ClockData(Shift::METHOD_UNKNOWN, $schedule->starts_at);
        if ($schedule->services->count()) { // Service breakout schedule

            return $schedule->services->reduce(function ($carry, ScheduleService $service) use ($clockIn, $schedule) {
                $rates = ShiftFactory::resolveRates(clone $clockIn, $service->getRates(), $schedule->client_id, $schedule->caregiver_id, $service->service_id, $service->payer_id);

                if ($service->g)
                if (empty($rates)) {
                    return $carry;
                }

                if ($schedule->fixed_rates) {
                    return $rates->caregiverRate();
                }
                return add((float) $carry, multiply($rates->caregiverRate(), (float)$service->duration));

            }, (float) 0.00);

        } else { // Regular

            $rates = ShiftFactory::resolveRates(clone $clockIn, $schedule->getRates(), $schedule->client_id, $schedule->caregiver_id, $schedule->service_id, $schedule->payer_id);

            if (empty($rates)) {
                return (float) 0.00;
            }

            if ($schedule->fixed_rates) {
                return $rates->caregiverRate();
            }
            return multiply($rates->caregiverRate(), divide($schedule->duration, 60));

        }
    }

    /**
     * Apply report filters.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $clientId
     * @param string|null $clientType
     * @param string|null $caregiverId
     * @param string $timezone
     * @return ProjectedBillingReport
     */
    public function applyFilters(?string $startDate, ?string $endDate, ?string $clientId, ?string $clientType, ?string $caregiverId, string $timezone) : self
    {
        $startDate = new Carbon($startDate . ' 00:00:00', $timezone);
        $endDate = new Carbon($endDate . ' 23:59:59', $timezone);

        $this->query->forRequestedBusinesses()
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->when(filled($clientId), function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->when(filled($caregiverId), function ($query) use ($caregiverId) {
                $query->where('caregiver_id', $caregiverId);
            })
            ->when(filled($clientType), function ($query) use ($clientType) {
                $query->whereHas('client', function ($query) use ($clientType) {
                    $query->where('client_type', $clientType);
                });
            });

        return $this;
    }
}
