<?php

namespace App\Claims;

use MyCLabs\Enum\Enum;

/**
 * ClaimRemitType Enum
 *
 * @method static ClaimRemitType TAKE_BACK()
 * @method static ClaimRemitType REMIT()
 */
class ClaimRemitType extends Enum
{
    private const TAKE_BACK = 'take-back';
    private const REMIT = 'remit';
}
