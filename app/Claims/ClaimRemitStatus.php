<?php

namespace App\Claims;

use App\BaseEnum;

/**
 * ClaimRemitStatus Enum
 *
 * @method static ClaimRemitStatus NOT_APPLIED()
 * @method static ClaimRemitStatus PARTIALLY_APPLIED()
 * @method static ClaimRemitStatus FULLY_APPLIED()
 */
class ClaimRemitStatus extends BaseEnum
{
    private const NOT_APPLIED = 'not_applied';
    private const PARTIALLY_APPLIED = 'partially_applied';
    private const FULLY_APPLIED = 'fully_applied';
}
