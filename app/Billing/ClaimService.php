<?php
namespace App\Billing;

use MyCLabs\Enum\Enum;

/**
 * ClaimService Enum
 *
 * @method static ClaimService HHA()
 * @method static ClaimService TELLUS()
 */
class ClaimService extends Enum
{
    private const HHA = 'HHA';
    private const TELLUS = 'TELLUS';
}
