<?php

namespace App\Reports;

use App\Billing\ClientAuthorization;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ThirdPartyPayerReport extends BaseReport
{
    /**
     * @var \Eloquent
     */
    protected $query;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var \Carbon\Carbon
     */
    protected $start;

    /**
     * @var \Carbon\Carbon
     */
    protected $end;

    /**
     * ThirdPartyPayerReport constructor.
     * @param ClientInvoiceQuery $query
     */
    public function __construct(ClientInvoiceQuery $query)
    {
        $this->query = $query->with(['items', 'client', 'clientPayer']);
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
     * Set instance timezone
     *
     * @param string $timezone
     * @return ThirdPartyPayerReport
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Create the query chain
     *
     * @param string $start
     * @param string $end
     * @param int $business
     * @param string|null $type
     * @param int|null $client
     * @param int|null $payer
     * @return ThirdPartyPayerReport
     */
    public function applyFilters(string $start, string $end, int $business, ?string $type, ?int $client, ?int $payer): self
    {
        $this->start = (new Carbon($start . ' 00:00:00', $this->timezone));
        $this->end = (new Carbon($end . ' 23:59:59', $this->timezone));

        $this->query->whereHas('items', function ($q) {
            $q->whereIn('invoiceable_type', ['shifts', 'shift_services'])
                ->whereBetween('date', [$this->start, $this->end]);
        });

        $this->query->forBusiness($business);

        if (filled($type)) {
            $this->query->whereHas('client', function ($q) use ($type) {
                $q->where('client_type', $type);
            });
        }

        if (filled($client)) {
            $this->query->where('client_id', $client);
        }

        if (filled($payer)) {
            $this->query->forPayer($payer);
        }

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        return $this->query->get()->map(function (ClientInvoice $invoice) {
            return $invoice->items
                ->whereIn('invoiceable_type', ['shifts', 'shift_services'])
                ->filter(function (ClientInvoiceItem $item) {
                    return Carbon::parse($item->date)->between($this->start, $this->end);
                })
                ->map(function (ClientInvoiceItem $item) use ($invoice) {
                    $data = [];
                    if ($shift = $item->getShift()) {
                        $data = $this->mapShiftRecord($invoice, $shift);
                    } else if ($shiftService = $item->getShiftService(true)) {
                        $data = $this->mapShiftServiceRecord($invoice, $shiftService);
                    } else {
                        return null;
                    }

                    $serviceAuth = $this->findServiceAuth($data['date'], $data['service_id'], $invoice->client->serviceAuthorizations);
                    return array_merge($data, [
                        'service_auth' => optional($serviceAuth)->service_auth_id,
                        'unit_type' => optional($serviceAuth)->unit_type,
                        'units' => $this->mapUnits(optional($serviceAuth)->unit_type, $data['hours'])
                    ]);
                })
                ->values()
                ->filter();
        })
        ->values()
        ->flatten(1);
    }

    /**
     * Map a Shift to a report row.
     *
     * @param ClientInvoice $invoice
     * @param Shift $shift
     * @return array
     */
    protected function mapShiftRecord(ClientInvoice $invoice, Shift $shift) : array
    {
        return [
            'shift_id' => $shift->id,
            'client_name' => $invoice->client->nameLastFirst,
            'client_id' => $invoice->client_id,
            'hic' => $invoice->client->hic,
            'dob' => (new Carbon($invoice->client->user->date_of_birth))->format('m/d/Y'),
            'caregiver' => optional($shift->caregiver)->nameLastFirst,
            'payer' => $invoice->clientPayer->payer_name,
            'rate' => $shift->getClientRate(),
            'hours' => $shift->duration(),
            'evv' => $shift->isVerified(),
            'service_id' => $shift->service->id,
            'service' => trim("{$shift->service->code} {$shift->service->name}"),
            'billable' => $shift->getAmountInvoiced(),
            'date' => $shift->checked_in_time->toDateString(),
            'start' => (new Carbon($shift->checked_in_time))->toDateTimeString(),
            'end' => (new Carbon($shift->checked_out_time))->toDateTimeString(),
            'code' => $invoice->client->medicaid_diagnosis_codes,
        ];
    }

    /**
     * Map a ShiftService to a report row.
     *
     * @param ClientInvoice $invoice
     * @param ShiftService $shiftService
     * @return array
     */
    protected function mapShiftServiceRecord(ClientInvoice $invoice, ShiftService $shiftService) : array
    {
        return [
            'shift_id' => $shiftService->shift->id,
            'client_name' => $invoice->client->nameLastFirst,
            'client_id' => $invoice->client_id,
            'hic' => $invoice->client->hic,
            'dob' => (new Carbon($invoice->client->user->date_of_birth))->format('m/d/Y'),
            'caregiver' => optional($shiftService->shift->caregiver)->nameLastFirst,
            'payer' => $invoice->clientPayer->payer_name,
            'rate' => $shiftService->getClientRate(),
            'hours' => $shiftService->duration,
            'evv' => $shiftService->shift->isVerified(),
            'service_id' => $shiftService->service->id,
            'service' => trim("{$shiftService->service->code} {$shiftService->service->name}"),
            'billable' => $shiftService->getAmountInvoiced(),
            'date' => $shiftService->shift->checked_in_time->toDateString(),
            'start' => (new Carbon($shiftService->shift->checked_in_time))->toDateTimeString(),
            'end' => (new Carbon($shiftService->shift->checked_out_time))->toDateTimeString(),
            'code' => $invoice->client->medicaid_diagnosis_codes,
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
