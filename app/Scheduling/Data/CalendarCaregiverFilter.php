<?php
namespace App\Scheduling\Data;

use MyCLabs\Enum\Enum;

class CalendarCaregiverFilter extends Enum
{
    private const ALL = "all";
    private const UNASSIGNED = "unassigned";

    static function ALL() { return new self(self::ALL); }
    static function UNASSIGNED() { return new self(self::UNASSIGNED); }
}