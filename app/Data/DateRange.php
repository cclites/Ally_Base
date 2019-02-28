<?php
namespace App\Data;

use App\Data\Traits\ReflectsToArray;
use Carbon\Carbon;
use JsonSerializable;

/**
 * Class DateRange
 * @package App\Data
 */
class DateRange implements JsonSerializable
{
    use ReflectsToArray;

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