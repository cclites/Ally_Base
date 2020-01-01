<?php
namespace App\Billing;

use App\BaseEnum;

/**
 * VisitEditActionEnum Enum
 *
 * @method static VisitEditActionEnum ACTIONS()
 * @method static VisitEditActionEnum acceptedCodes()
 * @method static VisitEditActionEnum acceptedDescriptions()
 * @method static VisitEditActionEnum findDescriptionByCode( $code )
 * @method static VisitEditActionEnum findCodeByDescription( $description )
 */
class VisitEditActionEnum extends BaseEnum
{
    private const ACTIONS = [

        [
            'code' => '10',
            'description' => "Confirmed visit with the client or the client's family member/representative and documented"
        ],
        [
            'code' => '11',
            'description' => "Supervisor approved change"
        ],
        [
            'code' => '12',
            'description' => "Updated client's phone number and documented"
        ],
        [
            'code' => '13',
            'description' => "Changed verification collection method and documented"
        ],
        [
            'code' => '14',
            'description' => "Timesheet received and signed by supervisor"
        ],
        [
            'code' => '15',
            'description' => "Visit rescheduled"
        ],
        [
            'code' => '16',
            'description' => "Updated client's address and documented"
        ],
        [
            'code' => '17',
            'description' => "Unverified visit; this service cannot be billed"
        ],
        [
            'code' => '18',
            'description' => "Service(s) cancelled or suspended until further notice"
        ],
        [
            'code' => '19',
            'description' => "Change in schedule"
        ],
        [
            'code' => '20',
            'description' => "Unspecified Action"
        ],
        [
            'code' => '21',
            'description' => "Other"
        ],
    ];

    /**
     * get an array of all actions and descriptions together
     *
     * @return array
     */
    public static function fullList()
    {
        return self::ACTIONS;
    }

    /**
     * get an array of the codes for every edit action
     *
     * @return array
     */
    public static function acceptedCodes()
    {
        return array_map( function( $r ){ return $r[ 'code' ]; }, self::ACTIONS );
    }

    /**
     * get an array of the descriptions for every edit action
     *
     * @return array
     */
    public static function acceptedDescriptions()
    {
        return array_map( function( $r ){ return $r[ 'description' ]; }, self::ACTIONS );
    }

    /**
     * take a known code and return the description
     *
     * @return array
     */
    public static function findDescriptionByCode( $code )
    {
        $index = array_search( $code, self::acceptedCodes() );
        return $index ? self::ACTIONS[ $index ][ 'description' ] : false;
    }

    /**
     * take a known description and return the code
     *
     * @return array
     */
    public static function findCodeByDescription( $description )
    {
        $index = array_search( $description, self::acceptedDescriptions() );
        return $index ? self::ACTIONS[ $index ][ 'code' ] : false;
    }
}
