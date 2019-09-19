<?php

namespace App\Claims;

use App\BaseEnum;

/**
 * ClaimRemitType Enum
 *
 * @method static ClaimRemitType REMIT()
 * @method static ClaimRemitType ACH()
 * @method static ClaimRemitType CC()
 * @method static ClaimRemitType CHECK()
 */
class ClaimRemitType extends BaseEnum
{
    private const REMIT = 'remit';
    private const ACH = 'ach';
    private const CC = 'cc';
    private const CHECK = 'check';
}
