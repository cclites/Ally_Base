<?php
namespace App\Scheduling\Data;

use App\Data\StringValueObject;
use DateTime;

class CalendarNextDayThreshold extends StringValueObject
{
    const DISABLED = "23:59:59"; // Calendar events will never span more than one day
    const DEFAULT = "09:00:00";

    public function __construct(string $value)
    {
        $this->assertTimestamp($value);
        $this->setValue($value);
    }

    private function assertTimestamp(string $value)
    {
        if (strlen($value) !== 8
            || DateTime::createFromFormat('H:i:s', '2:39:00') === false) {
            throw new \InvalidArgumentException("The provided value is not a valid timestamp.");
        }
    }
}