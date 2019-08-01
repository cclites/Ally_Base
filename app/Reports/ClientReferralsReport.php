<?php

namespace App\Reports;

use App\Billing\Queries\ClientInvoiceQuery;
use App\Client;
use App\Business\Payer;
use Carbon\Carbon;

class ClientReferralsReport extends BaseReport
{
    /**
     * @var string
     */
    public $timezone = 'America/New_York';

    /**
     * @var string
     */
    protected $start;

    /**
     * @var string
     */
    protected $end;

    /**
     * ClientReferralsReport constructor.
     * @param Client $query
     */
    public function __construct(Client $query)
    {
        $this->query = $query->with([
            'user',
            'addresses',
            'payers',
            'salesperson',
        ])->whereNotNull('referral_source_id');
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->query;
    }

    /**
     * Set the timezone of the report.
     *
     * @param string
     * @return this report
     */
    public function setTimezone(string $timezone) : self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Apply Filters
     *
     * @return this report
     */
    public function applyFilters(string $start, string $end, ?int $business, ?int $client, ?string $county): self
    {

        $start = (new Carbon($start . ' 00:00:00', $this->timezone));
        $end = (new Carbon($end . ' 23:59:59', $this->timezone));

        $this->query->whereHas('user', function($q) use($start, $end){
            $q->whereBetween('created_at', [$start, $end]);
        });

        if(filled($business)){
            $this->query->forBusinesses([$business]);
        }else{
            //This is oddly inconsistent, and unused for now. Shows
            //more results for a single business on a chain than it
            //does for all businesses on a chain.
            $ids = auth()->user()->role->businessChain->businesses->toArray();
            $this->query->forBusinesses($business);
        }

        if(filled($client)){
            $this->query->where('id', $client);
        }

        if(filled($county)){
            $this->query->whereHas('address', function ($q) use($county){
                $q->where("county", $county);
            });
        }

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results(): ?iterable
    {
        return $this->query
            ->get()
            ->unique()
            ->map(function($client){

                $invoiced = (new ClientInvoiceQuery())->forClient($client->id)
                            ->with('clientPayer')
                            ->get()
                            ->unique(['client_payer']);

                $payer = '';

                if(filled($invoiced)){
                    $payer = $invoiced->first()->clientPayer->payer_name;
                }

                return  [
                    'location' =>$client->business->name,
                    'county' => $client->addresses->first->county["county"] ? $client->addresses->first->county["county"] : "--",
                    'payer' => $payer,
                    'date' => ( new Carbon($client->created_at))->format('m/d/Y'),
                    'id' => $client->id,
                    'name' => $client->nameLastFirst,
                    'revenue' => $invoiced->sum('amount_paid'),
                    'salesperson' => $client->salesperson->fullName(),
                ];

            })
            ->values();
    }

}
