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
use http\Client;
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
        return $this->query
                    ->with(['client'])
                    ->take(1)
                    ->get()
                    ->map(function (ClientInvoice $invoice){

                        $items = $invoice->getItems();

                        $client_name = $invoice->client->nameLastFirst;
                        $hic = $invoice->client->hic;
                        $dob = $invoice->client->user->date_of_birth;
                        $payer = $invoice->clientPayer->payer_name;


                        return $items->map(function(ClientInvoiceItem $item) use($client_name, $hic, $dob, $payer)
                        {
                            $shift = $item->getShift();
                            $caregiver = $shift->caregiver->name;

                            //Log::info("SHIFT");
                            //Log::info(json_encode($shift));
                            //Log::info("\n");

                            return [
                                'client_name' => $client_name,
                                'hic' => (filled($hic) ? $ $hic : "-"),
                                'dob' => $dob,
                                'caregiver' => $caregiver,
                                'payer' => $payer,
                                'date' => $item->date,
                                'expiration_date' => (new Carbon( $item->date))->format('m/d/y'),
                                'units' => $item->units,
                                'rate' => $item->rate,
                                'billable' => $item->amount_due,
                                'start' => (new Carbon( $shift->checked_in_time ))->format('m/d/Y'),
                                'end' => (new Carbon( $shift->checked_out_time ))->format('m/d/Y'),
                                'hours' => $shift->hours,
                            ];

                        });



                    })
                    ->values();


    }
}
