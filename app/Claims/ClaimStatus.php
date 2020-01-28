<?php
namespace App\Claims;

use App\BaseEnum;

/**
 * ClaimStatus Enum
 *
 * @method static ClaimStatus CREATED()
 * @method static ClaimStatus TRANSMITTED()
 * @method static ClaimStatus ACCEPTED()
 * @method static ClaimStatus REJECTED()
 */
class ClaimStatus extends BaseEnum
{
    private const CREATED = 'CREATED';
    private const TRANSMITTED = 'TRANSMITTED';
    private const ACCEPTED = 'ACCEPTED';
    private const REJECTED = 'REJECTED';

    /**
     * Get the statuses that represent a Claim that
     * has not been transmitted yet.
     *
     * @return array
     */
    public static function notTransmittedStatuses()
    {
        return [
            self::CREATED()
        ];
    }

    /**
     * Get the statuses that represent a Claim that
     * has been transmitted.
     *
     * @return array
     */
    public static function transmittedStatuses()
    {
        return [
            self::TRANSMITTED()->getValue(),
            self::ACCEPTED()->getValue(),
            self::REJECTED()->getValue(),
        ];
    }
}
