<?php


namespace App\Shifts\Data;


use Carbon\Carbon;

class ClockData
{
    /**
     * @var string
     */
    public $method;
    /**
     * @var \Carbon\Carbon
     */
    public $time;

    public function __construct(string $method, string $utcDateTime = 'now')
    {
        $this->method = $method;
        $this->time = Carbon::parse($utcDateTime, 'UTC');
    }
}