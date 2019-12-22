<?php
namespace App\Scheduling;

use App\BaseEnum;

/**
 * OpenShiftStatus Enum
 *
 * @method static OpenShiftStatus REQUEST_APPROVED()
 * @method static OpenShiftStatus REQUEST_DENIED()
 * @method static OpenShiftStatus REQUEST_PENDING()
 * @method static OpenShiftStatus REQUEST_CANCELLED()
 * @method static OpenShiftStatus REQUEST_UNINTERESTED()
 * 
 */
class OpenShiftStatus extends BaseEnum
{
    private const REQUEST_APPROVED     = 'approved';
    private const REQUEST_DENIED       = 'denied';
    private const REQUEST_PENDING      = 'pending';
    private const REQUEST_CANCELLED    = 'cancelled';
    private const REQUEST_UNINTERESTED = 'uninterested';

    public static function isAcceptableStatus( $status )
    {
        // could probably change this to return the array itOpenShiftStatus and then call it using if( in_array() ) to extend the usefullness of this..
        return in_array( $status, [

            self::REQUEST_APPROVED,
            self::REQUEST_DENIED,
            self::REQUEST_PENDING,
            self::REQUEST_CANCELLED,
            self::REQUEST_UNINTERESTED,
        ]);
    }
}
