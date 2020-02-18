<?php


namespace App\Reports;


use Carbon\Carbon;

class AvailableShiftReport extends BaseReport
{
    protected $query;

    protected $start;

    protected $end;

    /**
     * @inheritDoc
     */
    public function query(): self
    {
        return $this->query;
    }

    public function applyFilters(string $start, string $end, ?int $client, ?string $city, ?int $service): self
    {

        $this->start = (new Carbon($start . ' 00:00:00'));
        $this->end = (new Carbon($end . ' 23:59:59'));

        $this->query->whereBetween('starts_at', [$this->start, $this->end]);

        if(filled($client)){
            $this->query->where('client_id', $client);
        }

        if(filled($city)){

        }

        if(filled($service)){}

        return $this;

    }

    /**
     * @inheritDoc
     */
    protected function results()
    {
        // TODO: Implement results() method.
    }
}