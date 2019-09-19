<?php

namespace App;

use MyCLabs\Enum\Enum;

class BaseEnum extends Enum
{
    /**
     * Get the Enum instance value from string.
     *
     * @param string $value
     * @return BaseEnum|null
     */
    public static function fromValue(string $value) : ?self
    {
        if ($key = self::search($value)) {
            return self::$key();
        }

        return null;
    }
}
