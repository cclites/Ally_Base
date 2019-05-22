<?php
namespace App\Billing;

use MyCLabs\Enum\Enum;

/**
 * ClaimService Enum
 *
 * @method static ClaimStatus NOT_SENT()
 * @method static ClaimStatus CREATED()
 * @method static ClaimStatus TRANSMITTED()
 * @method static ClaimStatus RETRANSMITTED()
 * @method static ClaimStatus ACCEPTED()
 * @method static ClaimStatus REJECTED()
 */
class ClaimStatus extends Enum
{
    private const NOT_SENT = 'NOT_SENT';
    private const CREATED = 'CREATED';
    private const TRANSMITTED = 'TRANSMITTED';
    private const RETRANSMITTED = 'RETRANSMITTED';
    private const ACCEPTED = 'ACCEPTED';
    private const REJECTED = 'REJECTED';
}
