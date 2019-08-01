<?php


namespace App\Reports;


use App\Client;
use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\SalesPerson;
use Carbon\Carbon;

use Log;

class InvoiceSummaryByMarketingReport extends BaseReport
{

    protected $timezone;

    protected $query;

    public $start;

    public $end;


    /**
     * InvoiceSummaryByMarketing constructor.
     */
    public function __construct(ClientInvoiceQuery $query)
    {
        $this->query = $query->with([
            'client',
            'client.user',
            'clientPayer',
            'client.salesperson'
        ]);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this;
    }

    /**
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone){

        $this->timezone = $timezone;

        return $this;
    }

    public function applyFilters(string $start, string $end, int $business, ?int $salesperson, ?int $client): self
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

        if(filled($client)){

            $this->query->whereHas('client', function($q) use($client){
                $q->where('id', $client);
            });

        }else{
            $this->query->whereHas('client', function($q){
                $q->whereNotNull('sales_person_id');
            });
        }


        return $this;
    }

    /**
     * @return Collection
     */
    protected function results(): iterable
    {
        $data = $this->query
                    ->get()
                    ->map(function (ClientInvoice $invoice){

                       return [
                           'client'=>$invoice->client->name,
                           'amount'=>$invoice->amount,
                           'salesperson'=>$invoice->client->salesperson ? $invoice->client->salesperson->fullName() : 'None',
                           'payer'=>optional($invoice->clientPayer)->payer_name,
                        ];

                    })
                    ->sortBy('salesperson')
                    ->values();

        return $data;
    }
}