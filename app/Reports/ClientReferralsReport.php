<?php

namespace App\Reports;

use App\Client;
use App\Business\Payer;
use Carbon\Carbon;

class ClientReferralsReport extends BaseReport
{
    /**
     * @var string
     */
    public $timezone = 'America/New_York';

    protected $start;

    protected $end;

    protected $business;

    protected $client;

    protected $county;

    /**
     * BusinessOfflineArAgingReport constructor.
     */
    public function __construct()
    {
        $this->query = Client::query()
                            ->whereNotNull('referral_source_id')
                            ->with(['user', 'address'])
                            ->orderByName();
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

        $this->start = (new Carbon($start . ' 00:00:00', $this->timezone));
        $this->end = (new Carbon($end . ' 23:59:59', $this->timezone));

        $this->query->whereHas('user', function($q) use($start, $end){
            $q->whereBetween('created_at', [$start, $end]);
        });

        if(filled($business)){
            $this->business = $business;
            $this->query->forBusinesses([$business]);
        }else{
            //This is oddly inconsistent, and unused for now. Shows
            //more results for a single business on a chain than it
            //does for all businesses on a chain.
            $ids = auth()->user()->role->businessChain->businesses->toArray();
            $this->query->forBusinesses($ids);
        }


        if(filled($client)){
            $this->client = $client;
            $this->query->where('id', $client);
        }

        if(filled($county)){
            $this->query->whereHas('address', function($q) use($county){
                $q->where('county', $county);
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
        $results = collect()->sortBy('payer');

        $this->query
            ->get()
            ->map(function($client) use($results){

                $client->payers->map(function($payer) use($client, $results){

                    $result =  [
                        'name' => $client->nameLastFirst,
                        'county' => $client->address["county"],
                        'payer' => $payer->payer_name,
                        'date' => ( new Carbon($client->created_at))->format('m/d/Y'),
                        'id' => $client->id
                    ];

                    $results->push($result);
                });

            });

        return $results;
    }

}
