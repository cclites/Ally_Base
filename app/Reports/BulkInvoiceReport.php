<?php


namespace App\Reports;

use Illuminate\Database\Eloquent\Builder;
use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\User;
use Carbon\Carbon;



class BulkInvoiceReport extends BaseReport
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
     * BulkInvoiceReport constructor.
     * @param ClientInvoiceQuery $query
     */
    public function __construct(ClientInvoiceQuery $query)
    {
        $this->query = $query->with('client');
    }

    /**
     * @return ClientInvoiceQuery|\Eloquent|Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Set the timezone of the report.
     *
     * @param string $timezone
     * @return ShiftHistoryReport
     */
    public function setTimezone(string $timezone) : self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Build the query
     *
     * @param string $start
     * @param string $end
     * @param int $business
     * @param int|null $client
     * @param string|null $type
     * @param int|null $active
     * @return BulkInvoiceReport
     */
    public function applyFilters(string $start, string $end, int $business, ?int $client, ?string $type, ?int $active): self
    {

        $start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('created_at', [$start, $end]);

        $this->query->forBusiness($business);

        if(filled($client)){
            $this->query->where('client_id', $client);
        }

        if(filled($type)){
            $this->query->whereHas('client', function($q) use($type){
                $q->where('client_type', $type);
            });
        }

        if(filled($active)){
            $this->query->whereHas('client', function($q) use($active){
                $q->whereHas('user', function($q) use($active){
                    $q->where('active', $active);
                });
            });
        }

        return $this;
    }

    /**
     * @return ClientInvoice[]|\Eloquent[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function results()
    {
       return $this->query
                    ->get()
                    ->map(function(ClientInvoice $invoice) {
                        return [
                            'invoice_id' => $invoice->id,
                            'client' => $invoice->client->nameLastFirst(),
                            'created_at' => $invoice->created_at->format('m/d/Y'),
                            'amount'=> $invoice->amount
                        ];
                    })
                    ->sort(function($a, $b){
                      if($a["client"] == $b["client"]){
                          return 0;
                      }
                      return ($a["client"] < $b["client"]) ? -1 : 1;
                    })
                    ->values();

    }
}