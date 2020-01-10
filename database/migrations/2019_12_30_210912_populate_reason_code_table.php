<?php

use App\Billing\VisitEditActionEnum;
use App\Billing\VisitEditReasonEnum;
use App\VisitEditAction;
use App\VisitEditReason;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateReasonCodeTable extends Migration
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
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach( self::REASONS as $reason ){

            VisitEditReason::create([

                'code' => $reason[ 'code' ],
                'description' => $reason[ 'description' ]
            ]);
        }

        foreach( self::ACTIONS as $action ){

            VisitEditAction::create([

                'code' => $action[ 'code' ],
                'description' => $action[ 'description' ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        VisitEditReason::truncate();
        VisitEditAction::truncate();
    }
}
