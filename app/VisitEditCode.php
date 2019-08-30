<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitEditCode extends Model
{
    protected $guarded = [];

    const VISIT_EDIT_CODES = [

        'actions' => [

            '10' => 'Confirmed visit with the client or the client\'s family member/representative and documented',
            '11' => 'Supervisor approved change',
            '12' => 'Updated client\'s phone number and documented',
            '13' => 'Changed verification collection method and documented',
            '14' => 'Timesheet received and signed by supervisor',
            '15' => 'Visit rescheduled',
            '16' => 'Updated client\'s address and documented',
            '17' => 'Unverified visit; this service cannot be billed',
            '18' => 'Service(s) cancelled or suspended until further notice',
            '19' => 'Change in schedule',
            '20' => 'unspecified Action',
            '21' => 'Other'
        ],
        'reasons' => [

            '105' => 'Services Provided Outside the Home - Supported by Service Plan',
            '110' => 'Fill-in for Regular Attendant or Assigned Staff',
            '115' => 'Client requested to change/cancel scheduled visit Scheduled visit; Scheduled visit has been cancelled due to the client\'s services being suspended',
            '120' => 'Attendant\'s identification number(s) does not match the scheduled shift',
            '121' => 'Attendant failed to report client\'s home',
            '125' => 'Multiple Calls for One Visit',
            '130' => 'Disaster or Emergency',
            '135' => 'Confirm Visits with no Schedule ( Warning, May result in audit )',
            '200' => 'Fixed location device on order or pending placement in the home',
            '205' => 'Small Alternative Device Pending Installation ( Warning: May result in audit )',
            '210' => 'Missing Small Alternative Device ( Warning: May result in audit )',
            '215' => 'Reversal of Cal In/Out Times ( Warning: May result in audit )',
            '300' => 'Client\'s phone line not working ( technical issue or natural disaster )',
            '305' => 'Attendant unable to connect to internet or EVV system down; Attendant entered invalid fixed location device code(s)',
            '310' => 'Attendant unable to use mobile device',
            '400' => 'Individual/Member Does Not Have Home Phone',
            '405' => 'Phone in use by client or individual in client\'s home',
            '410' => 'Client won\'t let attendant use phone',
            '800' => 'Address did not link to the client ( GPS )',
            '900' => 'Attendant failed to call in',
            '910' => 'Attendant failed to call in and out',
            '915' => 'Wrong Phone Number - Verified Services Were Delivered',
            '999' => 'Other ( Warning: May result in audit )'
        ]
    ];
}
