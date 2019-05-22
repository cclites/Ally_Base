<?php
namespace App\Scheduling\Data;

use MyCLabs\Enum\Enum;

class CalendarView extends Enum
{
    private const MONTH = "month";
    private const TIMELINE_WEEK = "timelineWeek";
    private const TIMELINE_DAY = "timelineDay";

    static function MONTH() { return new self(self::MONTH); }
    static function TIMELINE_WEEK() { return new self(self::TIMELINE_WEEK); }
    static function TIMELINE_DAY() { return new self(self::TIMELINE_DAY); }
}