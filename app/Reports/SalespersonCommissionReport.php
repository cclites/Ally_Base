<?php

namespace App\Reports;

use App\SalesPerson;
use App\Client;
use Carbon\Carbon;

class SalespersonCommissionReport extends BaseReport
{
    /**
     * The begin date.
     *
     * @var string
     */
    protected $startDate;

    /**
     * The end date.
     *
     * @var string
     */
    protected $endDate;

    /**
     * The salesperson ID.
     *
     * @var int
     */
    protected $salespersonId;

    /**
     * @var array
     */
    protected $businesses = [];

    /**
     * SalespersonCommissionReport constructor.
     */
    public function __construct()
    {
        $this->query = SalesPerson::query();
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
     * Filter the results to between two dates.
     *
     * @param string $start
     * @param string $end
     * @param $timezone
     * @return $this
     */
    public function forDates(string $start, string $end, string $timezone): self
    {
        // Format the date UTC to match the database.
        $startDate = Carbon::parse($start . ' 00:00:00', $timezone)->setTimezone('UTC')->toDateTimeString();
        $endDate = Carbon::parse($end . ' 23:59:59', $timezone)->setTimezone('UTC')->toDateTimeString();

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Filter by business IDs.
     *
     * @param array|null $businessIds
     * @return SalespersonCommissionReport
     */
    public function forBusinesses(array $businessIds = null) : self
    {
        $this->businesses = $businessIds ? $businessIds : [];
        return $this;
    }

    /**
     * Filter by sales person.
     *
     * @param null $salespersonId
     * @return $this
     */
    public function forSalespersonId($salespersonId = null) : self
    {
        $this->salespersonId = $salespersonId;
        return $this;
    }

    /**
     * @return iterable
     */
    protected function results(): iterable
    {

        $salesPeople = SalesPerson::select(['id', 'firstname', 'lastname'])
            ->whereIn('business_id', $this->businesses)
            ->get();

        $clientList = Client::forBusinesses($this->businesses)
                    ->whereIn('sales_person_id', $this->salespersonId ? [$this->salespersonId] : $salesPeople->pluck('id'))
                    ->whereHas('user', function ($q) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    })
                    ->get();

        return $salesPeople->map(function (SalesPerson $item) use ($clientList) {

            $clients = $clientList->where('sales_person_id', $item->id)
                        ->map(function(Client $client){
                            return [
                                'date'=>$client->created_at->format('m/d/Y'),
                                'name'=>$client->name,
                            ];
                        });
            return [
                'name' => $item->fullname(),
                'clients' => $clients,
                'count'=>$clients->count()
            ];
        })->values();
    }
}
