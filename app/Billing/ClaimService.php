<?php
namespace App\Billing;

use App\BaseEnum;

/**
 * ClaimService Enum
 *
 * @method static ClaimService HHA()
 * @method static ClaimService TELLUS()
 * @method static ClaimService CLEARINGHOUSE()
 * @method static ClaimService EMAIL()
 * @method static ClaimService FAX()
 */
class ClaimService extends BaseEnum
{
    private const HHA = 'HHA';
    private const TELLUS = 'TELLUS';
    private const CLEARINGHOUSE = 'CLEARINGHOUSE';
    private const EMAIL = 'EMAIL';
    private const FAX = 'FAX';
    private const DIRECT_MAIL = 'DIRECT_MAIL';
}
