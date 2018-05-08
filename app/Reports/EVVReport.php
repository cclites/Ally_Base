<?php


namespace App\Reports;

use App\Shift;
use UAParser\Parser;

class EVVReport extends ShiftsReport
{

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * EVVReport constructor.
     */
    public function __construct()
    {
        $this->query = Shift::where('checked_in', 1)
            ->with(['client', 'caregiver', 'business']);
    }
    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        if (! $this->rows) {
            $this->rows = $this->query()->get()->map(function(Shift $shift) {
                // Parse and append user agent
                $userAgent = $shift->checked_in_agent ?? $shift->checked_out_agent;
                $parser = Parser::create();
                $parsed = $parser->parse($userAgent);
                $shift->user_agent = json_decode(json_encode($parsed), true);
                return $shift;
            });
        }

        return $this->rows;
    }
}