<?php
namespace App\Billing;

use App\BaseEnum;

/**
 * VisitEditReasonEnum Enum
 *
 * @method static VisitEditReasonEnum REASONS()
 * @method static VisitEditReasonEnum acceptedCodes()
 * @method static VisitEditReasonEnum acceptedDescriptions()
 * @method static VisitEditReasonEnum findDescriptionByCode( $code )
 * @method static VisitEditReasonEnum findCodeByDescription( $description )
 */
class VisitEditReasonEnum extends BaseEnum
{
    private const REASONS = [

        [
            'code' => '105',
            'description' => 'Services Provided Outside the Home – Supported by Service Plan'
        ],
        [
            'code' => '110',
            'description' => 'Fill-in for Regular Attendant or Assigned Staff'
        ],
        [
            'code' => '115',
            'description' => "Client requested to change/cancel scheduled visit Scheduled visit has been cancelled due to the client's services being suspended"
        ],
        [
            'code' => '120',
            'description' => "Attendant's identification number (s) does not match the scheduled shift"
        ],
        [
            'code' => '121',
            'description' => "Attendant failed to report to client's home"
        ],
        [
            'code' => '125',
            'description' => 'Multiple Calls for One Visit'
        ],
        [
            'code' => '130',
            'description' => 'Disaster or Emergency'
        ],
        [
            'code' => '135',
            'description' => 'Confirm Visits with no Schedule (Warning: May result in audit)'
        ],
        [
            'code' => '200',
            'description' => 'Fixed location device on order or pending placement in the home'
        ],
        [
            'code' => '205',
            'description' => 'Small Alternative Device Pending Installation (Warning: May result in audit)'
        ],
        [
            'code' => '210',
            'description' => 'Missing Small Alternative Device (Warning: May result in audit)'
        ],
        [
            'code' => '215',
            'description' => 'Reversal of Call In/Out Times (Warning: May result in audit)'
        ],
        [
            'code' => '300',
            'description' => "Client's phone line not working (technical issue or natural disaster)"
        ],
        [
            'code' => '305',
            'description' => 'Attendant unable to connect to internet or EVV system down; Attendant entered invalid fixed location device code(s)'
        ],
        [
            'code' => '310',
            'description' => 'Attendant unable to use mobile device'
        ],
        [
            'code' => '400',
            'description' => 'Individual/Member Does Not Have Home Phone'
        ],
        [
            'code' => '405',
            'description' => 'Phone in use by client or individual in client\'s home'
        ],
        [
            'code' => '410',
            'description' => "Client won't let attendant use phone"
        ],
        [
            'code' => '800',
            'description' => 'Address did not link to the client (GPS)'
        ],
        [
            'code' => '900',
            'description' => 'Attendant failed to call in'
        ],
        [
            'code' => '905',
            'description' => 'Attendant failed to call out'
        ],
        [
            'code' => '910',
            'description' => 'Attendant failed to call in and out'
        ],
        [
            'code' => '915',
            'description' => 'Wrong Phone Number – Verified Services Were Delivered'
        ],
        [
            'code' => '999',
            'description' => 'Other (Warning: May result in audit)'
        ]
    ];

    /**
     * get an array of all reasons and descriptions together
     *
     * @return array
     */
    public static function fullList()
    {
        return self::REASONS;
    }

    /**
     * the default action for non-verified shifts
     *
     * @return array
     */
    public static function nonEvvDefault()
    {
        return 910;
    }

    /**
     * get an array of the codes for every edit reason
     *
     * @return array
     */
    public static function acceptedCodes()
    {
        return array_map( function( $r ){ return $r[ 'code' ]; }, self::REASONS );
    }

    /**
     * get an array of the descriptions for every edit reason
     *
     * @return array
     */
    public static function acceptedDescriptions()
    {
        return array_map( function( $r ){ return $r[ 'description' ]; }, self::REASONS );
    }

    /**
     * take a known code and return the description
     *
     * @return array
     */
    public static function findDescriptionByCode( $code )
    {
        $index = array_search( $code, self::acceptedCodes() );
        return $index ? self::REASONS[ $index ][ 'description' ] : false;
    }

    /**
     * take a known description and return the code
     *
     * @return array
     */
    public static function findCodeByDescription( $description )
    {
        $index = array_search( $description, self::acceptedDescriptions() );
        return $index ? self::REASONS[ $index ][ 'code' ] : false;
    }
}
