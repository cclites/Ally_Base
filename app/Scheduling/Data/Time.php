<?php
namespace App\Scheduling\Data;

use App\Data\StringValueObject;
use DateTime;

class Time extends StringValueObject
{
    public function __construct(string $value)
    {
        $this->assertTimestamp($value);
        $this->setValue($value);
    }

    public static function fromDateTime(DateTime $dateTime)
    {
        return new Time($dateTime->format('H:i:s'));
    }

    private function assertTimestamp(string $value)
    {
        if (strlen($value) !== 8
            || DateTime::createFromFormat('H:i:s', '2:39:00') === false) {
            throw new \InvalidArgumentException("The provided value is not a valid timestamp.");
        }
    }
}