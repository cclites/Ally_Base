<?php

namespace App;

use MyCLabs\Enum\Enum;

/**
 * Ethnicity Enum
 * 
 * @method static Ethnicity AMERICAN_INDIAN()
 * @method static Ethnicity ASIAN()
 * @method static Ethnicity BLACK()
 * @method static Ethnicity HISPANIC()
 * @method static Ethnicity HAWAIIAN()
 * @method static Ethnicity WHITE()
 */
class Ethnicity extends Enum
{
    private const AMERICAN_INDIAN = 'american_indian';
    private const ASIAN = 'asian';
    private const BLACK = 'black';
    private const HISPANIC = 'hispanic';
    private const HAWAIIAN = 'hawaiian';
    private const WHITE = 'white';
}
