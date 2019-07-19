<?php


namespace App\Reports;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;
use Carbon\Carbon;

use Log;

class PaymentSummaryByPayerReport extends BaseReport
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
        $this->query = $query->with([
            'items',

            'items.shift',
            'items.shift.service',
            'items.shift.services',

            'items.shiftService',
            'items.shiftService.service',

            'client',
            'client.user',
            'clientPayer',
        ]);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this->query;
    }

    /**
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function applyFilters(string $start, string $end, int $business, ?string $client_type, ?int $client, ?int $payer): self
    {
        $startDate = new Carbon($start . ' 00:00:00', $this->timezone);
        $endDate = new Carbon($end . ' 23:59:59', $this->timezone);

        //need to actually query by payment date maybe?
        $this->query->whereBetween('created_at', [$startDate, $endDate]);

        $this->query->forBusinesses([$business]);

        if(filled($client_type)){
            $this->query->whereHas('client', function($q) use($client_type){
                $q->where('client_type', $client_type);
            });
        }

        if(filled($client)){
            //$this->query->forClient($client);
            $this->query->whereHas('client', function($q) use($client){
                $q->where('id', $client);
            });
        }

        if(filled($payer)){
            $this->query->forPayer($payer);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    protected function results(): iterable
    {
        return $this->query->get()
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

                        return $data;
                    })
                    ->values()
                    ->filter();
            })
            ->flatten(1)
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
            'payer'=>$invoice->clientPayer->payer->name,
            'client_name'=>$invoice->client->nameLastFirst,
            'date'=>Carbon::parse($invoice->payments->last()->created_at, $this->timezone)->toDateString(),
            'client_type'=>ucwords(str_replace('_', ' ', $invoice->client->client_type)),
            'amount'=>$shift->getAmountCharged()
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
            'payer'=>$invoice->clientPayer->payer->name,
            'client_name'=>$invoice->client->nameLastFirst,
            'date'=>Carbon::parse($invoice->payments->last()->created_at, $this->timezone)->toDateString(),
            'client_type'=>ucwords(str_replace('_', ' ', $invoice->client->client_type)),
            'amount'=>$shiftService->getAmountCharged()
        ];
    }
}