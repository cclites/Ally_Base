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

    public $start;

    public $end;

    public $clientIds;

    /**
     * InvoiceSummaryByMarketing constructor.
     */
    public function __construct(ClientInvoiceQuery $query)
    //public function __construct()
    {
        $this->query = $query->with([
            'client',
            'client.user',
            'clientPayer',
            'client.salesperson',

            'business',
            'business.salesPeople'
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

        /*
        if(filled($salesperson)){
            $this->query->whereHas('salesperson', function($q) use($salesperson){
                $q->where('id', $salesperson);
            });
        }

        if(filled($client)){
            $this->query->where('client_id', $client);
        }


       */
        return $this;
    }

    /**
     * @return Collection
     */
    protected function results(): iterable
    {
        $data = $this->query->get();

        Log::info($data);
        /*
        $data = $this->query
                    ->get()
                    ->map(function (ClientInvoice $invoice){


                        Log::info(json_encode($invoice->client->salesperson));

                       return [
                           'client'=>$invoice->client->name,
                           'amount'=>$invoice->amount,
                           'salesperson'=>$invoice->client->referral_source_id,
                           'payer'=>$invoice->clientPayer->payer_name,
                        ];

                    })
                    ->sortBy('salesperson')
                    ->values();

        Log::info(json_encode($data));
*/
        return $data;

    }


}