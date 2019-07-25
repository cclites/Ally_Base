<?php


namespace App\Reports;


use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Shift;
use Carbon\Carbon;

class PaidBilledAuditReport extends BaseReport
{

    protected $query;

    protected $timezone;

    protected $start;

    protected $end;

    /**
     * PaidBilledAuditReport constructor.
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
            'client.serviceAuthorizations',
            'client.salesperson',

            'payments'
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this->query;
    }

    public function setTimezone($timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function applyFilters(string $start, string $end, int $business, ?string $salesperson): self
    {
        $this->start = (new Carbon($start . ' 00:00:00', 'UTC'));
        $this->end = (new Carbon($end . ' 23:59:59', 'UTC'));
        $this->query->whereBetween('created_at', [$this->start, $this->end]);

        $this->query->forBusiness($business);

        if(filled($salesperson)){
            $this->query->whereHas('client', function($q) use($salesperson){
                $q->where('sales_person_id', $salesperson);
            });
        }

        return $this;
    }
    /**
     * @return Collection
     */
    protected function results(): iterable
    {
        return $this->query
                ->get()
                ->map(function(ClientInvoice $invoice){
                    return $invoice->items
                        ->whereIn('invoiceable_type', ['shifts', 'shift_services'])

                        ->map(function (ClientInvoiceItem $item) use ($invoice) {
                            $data = [];
                            if ($item->invoiceable_type == 'shifts' && filled($item->shift)) {
                                if (empty($item->shift->service) && filled($item->shift->services)) {
                                    foreach ($item->shift->services as $service) {
                                        $data += $this->mapShiftServiceRecord($invoice, $service);
                                    }
                                } else {
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
                });
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
            'client_name' => $invoice->client->nameLastFirst,
            'caregiver' => optional($shift->caregiver)->nameLastFirst,
            'hours' => $shift->duration(),
            'service' => trim("{$shift->service->code} {$shift->service->name}"),
            'service_id' => $shift->service->id,
            'date' => Carbon::parse($shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateString(),
            'start' => Carbon::parse($shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'end' => Carbon::parse($shift->checked_out_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
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
            'client_name' => $invoice->client->nameLastFirst,
            'caregiver' => optional($shiftService->shift->caregiver)->nameLastFirst,
            'hours' => $shiftService->duration,
            'service' => trim("{$shiftService->service->code} {$shiftService->service->name}"),
            'service_id' => $shiftService->service->id,
            'date' => Carbon::parse($shiftService->shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateString(),
            'start' => Carbon::parse($shiftService->shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'end' => Carbon::parse($shiftService->shift->checked_out_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
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

}