<?php

namespace App\Reports;

use App\Billing\ClientAuthorization;
use App\Client;
use Carbon\Carbon;

class ServiceAuthUsageReport extends BaseReport
{
    /**
     * @var \Carbon\Carbon
     */
    protected $start;

    /**
     * @var \Carbon\Carbon
     */
    protected $end;

    /***
     * @var \App\Client
     */
    protected $client;

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
     * Set the client filter.
     *
     * @param Client $client
     * @return ServiceAuthUsageReport
     */
    public function setClient(Client $client) : self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Set the date range of service.
     *
     * @param string $startDate
     * @param string $endDate
     * @return self
     */
    public function setDateRange(string $startDate, string $endDate) : self
    {
        $this->start = Carbon::parse($startDate);
        $this->end = Carbon::parse($endDate);

        return $this;
    }

    /**
     * Convert period timezones to the report user timezone.
     *
     * @param array $period
     * @return array
     */
    public function convertPeriodTimezone(array $period) : array
    {
        $period[0] = Carbon::parse($period[0]->toDateString(), $this->getTimezone())->setTime(0, 0, 0);
        $period[1] = Carbon::parse($period[1]->toDateString(), $this->getTimezone())->setTime(23, 59, 59);

        return $period;
    }

    /**
     * Calculate and return usage stats for an auth during
     * a specific period.
     *
     * @param ClientAuthorization $auth
     * @param array $periods
     * @return iterable
     */
    protected function mapPeriodStats(ClientAuthorization $auth, array $periods) : iterable
    {
        return collect($periods)->map(function ($period) use ($auth) {
            $calculator = $auth->getCalculator();
            $allowedUnits = $auth->getUnits($period[0]);
            $allowedHours = $auth->getHours($period[0]);
            $confirmed = $calculator->getConfirmedUsage($period);
            $unconfirmed = $calculator->getUnconfirmedUsage($period);
            $scheduled = $calculator->getScheduledUsage($this->convertPeriodTimezone($period));
            $remaining = subtract($allowedUnits, add(add($confirmed, $scheduled), $unconfirmed));

            return [
                'period_display' => $period[0]->toDateString() . ' - ' . $period[1]->toDateString(),
                'period' => [$period[0]->toDateString(), $period[1]->toDateString()],
                'allowed_units' => $allowedUnits,
                'allowed_hours' => $allowedHours,
                'confirmed_units' => $confirmed,
                'confirmed_hours' => $auth->getHoursFromUnits($confirmed),
                'unconfirmed_units' => $unconfirmed,
                'unconfirmed_hours' => $auth->getHoursFromUnits($unconfirmed),
                'scheduled_units' => $scheduled,
                'scheduled_hours' => $auth->getHoursFromUnits($scheduled),
                'remaining_units' => $remaining,
                'remaining_hours' => $auth->getHoursFromUnits($remaining),
                'is_exceeded' => ($remaining < 0.00),
            ];
        })->values();
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        return $this->query
            ->where('client_id', $this->client->id)
            ->effectiveDuringRange($this->start, $this->end)
            ->get()
            ->map(function (ClientAuthorization $auth) {
                return [
                    // service auth data
                    'auth_id' => $auth->id,
                    'effective_start' => $auth->effective_start,
                    'effective_end' => $auth->effective_end,
                    'units' => $auth->units,
                    'unit_type' => $auth->unit_type,
                    'period' => $auth->period,
                    'name' => $auth->service_auth_id,
                    // client data
                    'client_id' => $auth->client->id,
                    'client_name' => $auth->client->name,
                    // service data
                    'service_id' => $auth->service->id,
                    'service_name' => $auth->service->name,
                    'service_code' => $auth->service->code,
                    // period stats
                    'periods' => $this->mapPeriodStats($auth, $auth->getPeriodsForRange($this->start, $this->end)),
                ];
            })
            ->sortBy('service_name')
            ->values();
    }

    /**
     * Get the timezone to use for the report.
     *
     * @return string
     */
    protected function getTimezone() : string
    {
        return $this->client->getTimezone();
    }
}
