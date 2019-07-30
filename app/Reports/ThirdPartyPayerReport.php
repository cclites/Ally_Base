<?php

namespace App\Reports;

use App\Billing\ClientAuthorization;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Http\Response;
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
        $this->query = $query->with([
            'items',

            'items.shift',
            'items.shift.service',
            'items.shift.services',
            'items.shift.shiftFlags',
            'items.shift.caregiver',
            'items.shift.caregiver.user',

            'items.shiftService',
            'items.shiftService.service',
            'items.shiftService.shift',
            'items.shiftService.shift.caregiver',
            'items.shiftService.shift.shiftFlags',
            'items.shiftService.shift.caregiver.user',

            'client',
            'client.user',
            'clientPayer',
            'client.serviceAuthorizations'
        ]);
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
        $this->start = (new Carbon($start . ' 00:00:00', 'UTC'));
        $this->end = (new Carbon($end . ' 23:59:59', 'UTC'));

        // Base the date range on the creation date of the invoice
        // so we can properly get old imported timesheets from previous
        // weeks in the current week.
        $this->query->whereBetween('created_at', [$this->start, $this->end]);
//        $this->query->whereHas('items', function ($q) {
//            $q->whereIn('invoiceable_type', ['shifts', 'shift_services'])
//                ->whereBetween('date', [$this->start, $this->end]);
//        });

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
//                ->filter(function (ClientInvoiceItem $item) {
//                    return Carbon::parse($item->date)->between($this->start, $this->end);
//                })
                ->map(function (ClientInvoiceItem $item) use ($invoice) {
                    $data = [];
                    if ($item->invoiceable_type == 'shifts' && filled($item->shift)) {
                        if (empty($item->shift->service) && filled($item->shift->services)) {
                            // Shift has been modified after invoice to contain a service
                            // breakout instead of a single service.
                            foreach ($item->shift->services as $service) {
                                $data += $this->mapShiftServiceRecord($invoice, $service);
                            }
                        } else {
                            // Regular shift
                            $data += $this->mapShiftRecord($invoice, $item->shift);
                        }
                    } else if ($item->invoiceable_type == 'shift_services' && filled($item->shiftService)) {
                        $data += $this->mapShiftServiceRecord($invoice, $item->shiftService);
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
        ->flatten(1)
        ->sortBy('client_name')
        ->values();
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
            'invoice_id' => $invoice->id,
            'invoice_name' => $invoice->name,
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
            'date' => Carbon::parse($shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateString(),
            'start' => Carbon::parse($shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'end' => Carbon::parse($shift->checked_out_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'code' => $invoice->client->medicaid_diagnosis_codes,
            'billable' => multiply(floatval($shift->duration()), floatval($shift->getClientRate())),
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
            'invoice_id' => $invoice->id,
            'invoice_name' => $invoice->name,
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
            'date' => Carbon::parse($shiftService->shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateString(),
            'start' => Carbon::parse($shiftService->shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'end' => Carbon::parse($shiftService->shift->checked_out_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'code' => $invoice->client->medicaid_diagnosis_codes,
            'billable' => multiply(floatval($shiftService->duration), floatval($shiftService->getClientRate())),
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

    public function download()
    {
        $this->rows = $this->results()->map(function ($row) {
            return [
                'client_name' => $row['client_name'],
                'client_id' => $row['client_id'],
                'invoice' => $row['invoice_name'],
                'hic' => $row['hic'] ?: '-',
                'client_dob' => $row['dob'] ?: '-',
                'diagnosis_code' => $row['code'] ?: '-',
                'caregiver' => $row['caregiver'] ?: '-',
                'payer' => $row['payer'] ?: '-',
                'service code & type' => $row['service'] ?: '-',
                'Authorization Number' => $row['service_auth'] ?: '-',
                'date' => $row['date'],
                'start' => $row['start'],
                'end' => $row['end'],
                'units' => $row['units'],
                'hours' => $row['hours'],
                'Cost/Hour' => $row['rate'],
                'EVV' => $row['evv'] ? 'Yes' : 'No',
                'total_billable' => $row['billable'],
            ];
        });

        parent::download();
    }
}
