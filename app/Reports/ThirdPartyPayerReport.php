<?php

namespace App\Reports;

use App\Billing\ClientAuthorization;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Queries\ClientInvoiceQuery;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
        $this->query = $query->with(['client', 'client.payers']);
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


    /**
     * Create the query chain
     *
     * @param string $start
     * @param string $end
     * @param int $business
     * @param string|null $type
     * @param int|null $client
     * @param int|null $payer
     * @return ThirdPartyPayerReport
     */
    public function applyFilters(string $start, string $end, int $business, ?string $type, ?int $client, ?int $payer): self
    {
        $start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('created_at', [$start, $end]);

        $this->query->forBusiness($business);

        if(filled($type)){
            $this->query->whereHas('client', function($q) use($type){
                $q->where('client_type', $type);
            });
        }

        if(filled($client)){
            $this->query->whereHas('client', function($q) use($client){
                $q->where('id', $client);
            });
        }

        if(filled($payer)){
            $this->query->whereHas('client', function($q) use($payer){
                $q->whereHas('payers', function($q1) use($payer){
                    $q1->where('id', $payer);
                });
            });
        }

        return $this;
    }

    /**
     * Get the matching service authorization for the given service and date.
     *
     * @param string $date
     * @param int $serviceId,
     * @param Collection|null $serviceAuths
     * @return ClientAuthorization|null
     */
    protected function findServiceAuth(string $date, int $serviceId, ?Collection $serviceAuths) : ?ClientAuthorization
    {
        if (empty($serviceAuths) || $serviceAuths->isEmpty()) {
            return null;
        }

        return $serviceAuths->where('service_id', $serviceId)
            ->where('effective_start', '<=', $date)
            ->where('effective_end', '>=', $date)
            ->first();
    }

    /**
     * Return the collection of rows matching report criteria.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        $itemsArray = collect();

        $this->query
            ->with(['client'])
            ->get()
            ->map(function (ClientInvoice $invoice) use($itemsArray){

                $items = $invoice->getItems();

                $items->map(function(ClientInvoiceItem $item) use($invoice, $itemsArray)
                {
                    $shift = ($item->getShift()) ? $item->getShift() : $item->getShiftService();

                    if(!filled($shift)){
                        return $item; //Can't continue, so return the item.
                    }

                    $serviceAuth = '';
                    if(filled($shift->schedule)){
                        $serviceAuth = $this->findServiceAuth($shift->schedule->getStartDateTime(), $shift->service->id, $invoice->client->serviceAuthorizations);
                    }

                    $data =  [
                        'client_name' => $invoice->client->nameLastFirst,
                        'hic' => $invoice->client->hic,
                        'dob' => (new Carbon($invoice->client->user->date_of_birth))->format('m/d/Y'),
                        'caregiver' => $shift->caregiver->name,
                        'payer' => $invoice->clientPayer->payer_name,
                        'units' => $item->units,
                        'rate' => $shift->getClientRate(),
                        'hours' => $shift->duration(),
                        'evv' => $shift->isVerified(),
                        'service' => $shift->service->name . " " . $shift->service->code,
                        'billable' => $shift->costs()->getClientCost(false),
                        'date' => $shift->checked_in_time->toDateString(),
                        'start' => (new Carbon($shift->checked_in_time))->toDateTimeString(),
                        'end' => (new Carbon($shift->checked_out_time))->toDateTimeString(),
                        'service_auth' => optional($serviceAuth)->service_auth_id,
                    ];

                    $itemsArray->push($data);

                });

            })
            ->values();

        return $itemsArray;
    }
}
