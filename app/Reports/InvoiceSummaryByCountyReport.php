<?php


namespace App\Reports;

use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Reports\BaseReport;
use Carbon\Carbon;

class InvoiceSummaryByCountyReport extends BaseReport
{

    /**
     * @var ClientInvoiceQuery
     */
    protected $query;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * InvoiceSummaryByCountyReport constructor.
     */
    public function __construct(ClientInvoiceQuery $query)
    {
        $this->query = $query->with([
            'client',

            'items.shift',
            'items.shift.service',
            'items.shift.services',

            'items.shiftService',
            'items.shiftService.service',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Apply filters to query
     *
     * @param string $start
     * @param string $end
     * @param int $business
     * @param int|null $client
     * @return InvoiceSummaryByCountyReport
     */
    public function applyFilters(string $start, string $end, int $business, ?int $client): self
    {
        $start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('created_at', [$start, $end]);

        $this->query->forBusinesses([$business]);

        if (filled($client)) {
            $this->query->where('client_id', $client);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    protected function results(): iterable
    {
        return $this->query->get()->map(function (ClientInvoice $invoice) {

            $hours = 0;

            foreach($invoice->items as $item){

                if ($item->invoiceable_type == 'shifts' && filled($item->shift)){
                    if (empty($item->shift->service) && filled($item->shift->services)) {
                        foreach ($item->shift->services as $service) {
                            try{
                                $hours += $service->duration();
                            }catch (\Exception $e){
                                //swallow
                            }
                        }
                    } else {
                        $hours += $item->shift->duration();
                    }
                } else if ($item->invoiceable_type == 'shift_services' && filled($item->shiftService)) {

                    try{
                        $hours += $item->shiftService->duration();
                    }catch (\Exception $e){
                        //swallow
                    }
                }
            }

            return [
                'client_name'=>$invoice->client->nameLastFirst,
                'client_id' => $invoice->client->id,
                'county'=>$invoice->client->addresses->first->county["county"] ? $invoice->client->addresses->first->county["county"] : "No county listed",
                'amount'=>$invoice->amount,
                'hours' => $hours,
            ];

        });
    }


}