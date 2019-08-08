<?php


namespace App\Reports;


use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\Client;
use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use App\SalesPerson;
use Carbon\Carbon;

use Illuminate\Http\Response;
use Log;

class InvoiceSummaryBySalespersonReport extends BaseReport
{

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var ClientInvoiceQuery
     */
    protected $query;

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

    /**
     * @param string $start
     * @param string $end
     * @param int $business
     * @param int|null $salesperson
     * @param int|null $client
     * @return InvoiceSummaryByMarketingReport
     */
    public function applyFilters(string $start, string $end, int $business, ?int $salesperson, ?int $client): self
    {
        $start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('created_at', [$start, $end]);

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
                           'client'=>$invoice->client->nameLastFirst,
                           'amount'=>$invoice->amount,
                           'salesperson'=>$invoice->client->salesperson ? $invoice->client->salesperson->nameLastFirst : 'None',
                           'payer'=>optional($invoice->clientPayer)->payer_name
                        ];

                    })->toArray();

        return $this->sort($data);
    }

    protected function sort($data) : iterable
    {
        $salespersons = [];
        $clients = [];

        foreach ($data as $item){
            $salespersons[] = $item['salesperson'];
            $clients[] = $item['client'];
        }

        array_multisort($salespersons, SORT_ASC,
                              $clients, SORT_ASC,
                              $data
            );

        $collection = collect();
        foreach($data as $item){
            $collection->push($item);
        }

        return $collection;
    }

    /**
     * Get the PDF printed output of the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function print($data, $totals) : \Illuminate\Http\Response
    {
        $html = View::make('business.reports.print.invoice_summery_by_salesperson',['data'=>$data, 'totals'=>$totals])->render();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoices_summary_by_salesperson.pdf"'
            )
        );
    }
}