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
        $this->query = $query->with(['client']);
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
        $start = (new Carbon($start . ' 00:00:00', 'UTC'));
        $end = (new Carbon($end . ' 23:59:59', 'UTC'));
        $this->query->whereBetween('created_at', [$start, $end]);

        $this->query->forBusiness($business);

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

            return [
                'client_name'=>$invoice->client->nameLastFirst,
                'client_county'=>$invoice->client->addresses->first->county["county"] ? $invoice->client->addresses->first->county["county"] : "--",
                'amount'=>$invoice->amount

            ];

        })->values();
    }


}