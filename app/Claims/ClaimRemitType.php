<?php

namespace App\Claims;

use App\BaseEnum;

/**
 * ClaimRemitType Enum
 *
 * @method static ClaimRemitType TAKE_BACK()
 * @method static ClaimRemitType REMIT()
 */
class ClaimRemitType extends BaseEnum
{
    private const TAKE_BACK = 'take-back';
    private const REMIT = 'remit';
}
