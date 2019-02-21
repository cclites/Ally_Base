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
    public $start;

    /** @var \Carbon\Carbon */
    public $end;

    public function __construct(Carbon $start, Carbon $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}