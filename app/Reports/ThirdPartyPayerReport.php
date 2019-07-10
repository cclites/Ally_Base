<?php

namespace App\Reports;

use App\Billing\ClientAuthorization;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\ClientPayer;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Payer;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

use Log;

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
     * ThirdPartyPayerReport constructor.
     * @param ClientInvoiceQuery $query
     */
    public function __construct(ClientInvoiceQuery $query)
    {
        $this->query = $query->with('client');
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


    public function applyFilters(string $start, string $end, int $business, ?string $type, ?int $client, ?int $caregiver, ?int $payer): self
    {
        $start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('created_at', [$start, $end]);

        $this->query->forBusiness($business);

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        $invoices = $this->query
                    ->with(['client', 'payments'])
                    ->take(1)
                    ->get()
                    ->values();

        foreach ($invoices as $invoice){

            //$items = $invoice->items;

            $payer = $invoice->clientPayer;

            //Log:info(json_encode($payer));

            Log::info($payer->payer_name);

            //foreach ($items as $item){



                /*
                $payerId = ClientPayer::where('id', $item->invoice["client_payer_id"])->pluck('payer_id');

                if(filled($payerId)){
                    $item->payer = Payer::where('id', $payerId)->pluck('name')->first();
                }

                //$s = print_r($shift, true);
                //Log::info($s);

                Log::info(json_encode($item->payer));
                Log::info("\n");
                */
            //}


            //Log::info(json_encode($shifts));
            //Log::info("\n");

            //$client = $invoice->client;
            //$clientPayer = $invoice->getClientPayer();
            //$business = $client->business;
            //$payments = $invoice->payments;



            /*
            Log::info(json_encode($items->getShiftServices()));
            Log::info("\n");
            */

            //$invoiceableServices = $this->getInvoicedServicesQuery($invoice);

            //Log::info(json_encode($invoiceableServices));
            //Log::info("\n");

            //$invoiceableShifts = $this->getInvoicedShiftsQuery($invoice);
            //Log::info(json_encode($invoiceableShifts));
            //Log::info("\n");

        }

        return $invoices;
    }




}
