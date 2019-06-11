<?php
namespace App\Billing;

use MyCLabs\Enum\Enum;

/**
 * ClaimService Enum
 *
 * @method static ClaimService HHA()
 * @method static ClaimService TELLUS()
 * @method static ClaimService CLEARINGHOUSE()
 * @method static ClaimService EMAIL()
 * @method static ClaimService FAX()
 */
class ClaimService extends Enum
{
    private const HHA = 'HHA';
    private const TELLUS = 'TELLUS';
    private const CLEARINGHOUSE = 'CLEARINGHOUSE';
    private const EMAIL = 'EMAIL';
    private const FAX = 'FAX';
}
