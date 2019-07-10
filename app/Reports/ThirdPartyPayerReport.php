<?php

namespace App\Reports;

use App\Billing\ClientAuthorization;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ThirdPartyPayerReport extends BaseReport
{
    /**
     * @var int
     */
    protected $client;

    /**
     * @var int
     */
    protected $caregiver;

    /**
     * @var string
     */
    protected $clientType;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = Shift::with(['caregiver', 'client', 'services', 'service', 'services.service', 'client.serviceAuthorizations'])
            ->forRequestedBusinesses()
            ->whereConfirmed();
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
     * Filter the results to between two dates.
     *
     * @param string $start
     * @param string $end
     * @param string $timezone
     * @return $this
     */
    public function forDates(string $start, string $end, ?string $timezone = null) : self
    {
        if (empty($timezone)) {
            $timezone = 'America/New_York';
        }
        $startDate = new Carbon($start . ' 00:00:00', $timezone);
        $endDate = new Carbon($end . ' 23:59:59', $timezone);
        $this->between($startDate, $endDate);

        return $this;
    }

    /**
     * Filter the results for a certain client type.
     *
     * @param string|null $clientType
     * @return $this
     */
    public function forClientType(?string $clientType = null) : self
    {
        $this->clientType = $clientType;

        return $this;
    }

    /**
     * Filter the results for a certain client.
     *
     * @param string|null $id
     * @return $this
     */
    public function forClient(?string $id = null) : self
    {
        $this->client = $id;

        return $this;
    }

    /**
     * Filter the results for a certain caregiver.
     *
     * @param string|null $id
     * @return $this
     */
    public function forCaregiver(?string $id = null) : self
    {
        $this->caregiver = $id;

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        $query = $this->query();

        if (filled($this->client)) {
            $query->where('client_id', $this->client);
        }

        if (filled($this->caregiver)) {
            $query->where('caregiver_id', $this->caregiver);
        }

        if (filled($this->clientType)) {
            $query->whereHas('client', function ($q) {
                $q->where('client_type', $this->clientType);
            });
        }

        $shifts = $query->get();
        $services = collect([]);
        foreach ($shifts as $shift) {
            if (filled($shift->service_id)) {

                $services->push($this->mapShiftRecord($shift));

            } else if ($shift->services->isNotEmpty()) {

                $start = $shift->checked_in_time;
                foreach ($shift->services as $shiftService) {
                    $end = $start->copy()->addHours($shiftService->duration);
                    $services->push($this->mapShiftServiceRecord($shift, $shiftService, $start, $end));
                    $start = $end->copy()->addSeconds(1);
                }

            }
        }

        return $services->map(function ($service) {
            $serviceAuth = $this->findServiceAuth($service['date'], $service['service_id'], $service['client']->serviceAuthorizations);
            return array_merge($service, [
                'service_auth' => optional($serviceAuth)->service_auth_id,
                'unit_type' => optional($serviceAuth)->unit_type,
                'units' => $this->mapUnits(optional($serviceAuth)->unit_type, $service['hours'])
            ]);
        });
    }

    /**
     * Map a Shift to a report row.
     *
     * @param Shift $shift
     * @return array
     */
    protected function mapShiftRecord(Shift $shift) : array
    {
        return [
            'caregiver_id' => $shift->caregiver_id,
            'caregiver_name' => $shift->caregiver->nameLastFirst,
            'client' => $shift->client,
            'client_id' => $shift->client_id,
            'client_name' => $shift->client->nameLastFirst,
            'service_id' => $shift->service->id,
            'service' => trim("{$shift->service->code} {$shift->service->name}"),
            'hours' => $shift->duration(),
            'rate' => $shift->getClientRate(),
            'evv' => $shift->isVerified(),
            'billable' => $shift->costs()->getClientCost(false),
            'date' => $shift->checked_in_time->toDateString(),
            'start' => $shift->checked_in_time->toDateTimeString(),
            'end' => $shift->checked_out_time->toDateTimeString(),
        ];
    }

    /**
     * Map a ShiftService to a report row.
     *
     * @param Shift $shift
     * @param ShiftService $shiftService
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    protected function mapShiftServiceRecord(Shift $shift, ShiftService $shiftService, Carbon $start, Carbon $end) : array
    {
        return [
            'caregiver_id' => $shift->caregiver_id,
            'caregiver_name' => $shift->caregiver->nameLastFirst,
            'client' => $shift->client,
            'client_id' => $shift->client_id,
            'client_name' => $shift->client->nameLastFirst,
            'service_id' => $shiftService->service->id,
            'service' => trim("{$shiftService->service->code} {$shiftService->service->name}"),
            'hours' => $shiftService->duration,
            'rate' => $shiftService->client_rate,
            'evv' => $shift->isVerified(),
            'billable' => $shiftService->getAmountInvoiced(), // TODO: this isn't correct?
            'date' => $start->toDateString(),
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
        ];
    }

    /**
     * Map service auth units based on duration.
     *
     * @param null|string $unitType
     * @param float $hours
     * @return string
     */
    protected function mapUnits(?string $unitType, float $hours) : string
    {
        switch ($unitType) {
            case '15m':
                return (string) (floatval($hours) * floatval(60)) / floatval(15);
            case 'fixed':
                return 1;
            case 'hourly':
                return $hours;
            default:
                return '-';
        }
    }

    /**
     * Get the matching service authorization for the given service and date.
     *
     * @param string $date
     * @param int $serviceId,
     * @param Collection|null $serviceAuths
     * @return ClientAuthorization|null
     */
    protected function findServiceAuth(string $date, int $serviceId, ?Collection $serviceAuths) : ?ClientAuthorization
    {
        if (empty($serviceAuths) || $serviceAuths->isEmpty()) {
            return null;
        }

        return $serviceAuths->where('service_id', $serviceId)
            ->where('effective_start', '<=', $date)
            ->where('effective_end', '>=', $date)
            ->first();
    }
}
