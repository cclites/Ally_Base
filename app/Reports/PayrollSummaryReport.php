<?php


namespace App\Reports;


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

    public function __construct()
    {
        $this->query = PayrollReport::query();
    }


    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
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

    public function applyFilters(string $start, string $end, int $business, ?string $client_type, ?int $caregiver): self
    {

    }

    protected function results() : ?iterable
    {
        $data = $report->forRequestedBusinesses()
            ->forDates($request->start, $request->end)
            ->forCaregiver($request->caregiver)
            ->rows();
    }

}