<?php


namespace App\Reports;


use App\Billing\CaregiverInvoice;

use Log;

class PayrollSummaryReport extends BaseReport
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
     * PayrollSummaryReport constructor.
     * @param CaregiverInvoiceQuery $query
     */
    public function __construct(CaregiverInvoice $query)
    {
        $this->query = $query;
    }


    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this->query->with('items');
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

    public function applyFilters(string $start, string $end, int $business, ?string $client_type, ?int $caregiver): self
    {
        $this->query->whereBetween('created_at', [$start, $end]);

        /*
        $this->query->whereHas('caregiver.shifts', function($q) use($business){
            $q->where('business_id', $business);
        });

        if(filled($client_type)){
            $this->query->whereHas('client', function($q) use($client_type){
                $q->where('client_type', $client_type);
            });
        }
*/
        if(filled($caregiver)){
            $this->query->forCaregiver($caregiver);
        }$this->query->get();


        return $this;
    }

    protected function results() : ?iterable
    {

        $results = $this->query->take(5)->get();

        Log::info($results);

        return $results;
        /*
        $data = $report->forRequestedBusinesses()
            ->forDates($request->start, $request->end)
            ->forCaregiver($request->caregiver)
            ->rows();*/
    }

}