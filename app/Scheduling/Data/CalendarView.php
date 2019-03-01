<?php
namespace App\Scheduling\Data;

use App\Data\StringValueObject;

class CalendarView extends StringValueObject
{
    const MONTH = "month";
    const TIMELINE_WEEK = "timelineWeek";
    const TIMELINE_DAY = "timelineDay";

    public function __construct(string $value)
    {
        $this->assertValueInConstants($value);
        $this->setValue($value);
    }
}