<?php
namespace App\Scheduling\Data;

use App\Data\StringValueObject;

class CalendarCaregiverFilter extends StringValueObject
{
    const ALL = "all";
    const UNASSIGNED = "unassigned";

    public function __construct(string $value)
    {
        $this->assertValueInConstants($value);
        $this->setValue($value);
    }
}