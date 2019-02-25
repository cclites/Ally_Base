<?php
namespace App\Data;

use Carbon\Carbon;

/**
 * Class DateRange
 * @package App\Data
 */
class DateRange
{
    /** @var \Carbon\Carbon */
    private $start;

    /** @var \Carbon\Carbon */
    private $end;

    public function __construct(Carbon $start, Carbon $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function start(): Carbon
    {
        return $this->start->copy();
    }

    public function end(): Carbon
    {
        return $this->end->copy();
    }
}