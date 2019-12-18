<?php

namespace App\Http\Controllers\Caregivers;

use App\Business;
use App\CaregiverScheduleRequest;
use App\Responses\ErrorResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;
use App\Responses\SuccessResponse;

class ScheduleController extends BaseController
{
    public function index()
    {
        return view('caregivers.schedule');
    }

    public function events(Request $request, ScheduleAggregator $aggregator)
    {
        $caregiver = auth()->user()->role;
        $aggregator->where('caregiver_id', $caregiver->id);

        $start = new Carbon(
            $request->input('start', date('Y-m-d', strtotime('First day of this month'))),
            $caregiver->businesses->first()->timezone ?? 'America/New_York'
        );
        $end = new Carbon(
            $request->input('end', date('Y-m-d', strtotime('First day of next month'))),
            $caregiver->businesses->first()->timezone ?? 'America/New_York'
        );

        $schedules = $aggregator->getSchedulesBetween($start, $end);

        $events = new ScheduleEventsResponse($schedules);
        return $events;
    }

    /**
     * I think this belongs in the schedule controller because an 'open-shift' is an attribute of a schedule, not its own entity..
     */
    public function openShifts()
    {
        $caregiver = auth()->user()->role;

        if( request()->filled( 'json' ) ){

            // get business dynamically, needs to be plural because 'scopeForRequestedBusinesses' will pick it up
            $businessId = request()->input( 'businesses', null );

            if( empty( $businessId ) ) return new ErrorResponse( 500, 'Schedules could not be received' );

            $business = Business::findOrFail( $businessId );
            $setting = $business->open_shifts_setting;

            if( !in_array( $setting, [ Business::OPEN_SHIFTS_LIMITED, Business::OPEN_SHIFTS_UNLIMITED ] ) ) return new ErrorResponse( 500, 'Invalid registry setting' );

            $query = Schedule::forRequestedBusinesses()
                ->with([ 'client' ])
                ->inTheNextMonth( $business->timezone )
                ->whereOpen()
                ->ordered();

            if( $setting === Business::OPEN_SHIFTS_LIMITED ){
                // check to see if you should only grab related caregiver-cients

                $query->whereHas( 'client.rates', function ( $q ) use ( $caregiver ) {

                    $q->where( 'caregiver_id', $caregiver->id );
                });
            }

            // get all of the caregver's existing schedules in the same time frame
            $caregivers_schedule = Schedule::forRequestedBusinesses()
                ->forCaregiver( $caregiver )
                ->inTheNextMonth( $business->timezone )
                ->ordered()
                ->get();

            // foreach fucking schedule that comes back from the query, i need to run it against every schedule that the caregiver has to ensure it owont overlap.. BUT
            // if I am on some mark zuckerburg shit, I'll know how to make the query not need to match for all of them.. what is this algorithm

            $schedules = $query->get()
                ->filter( function( Schedule $schedule ) use( $caregivers_schedule ){

                    $schedule_start = $schedule->starts_at;
                    $schedule_end   = $schedule->getEndDateTime();
                    foreach( $caregivers_schedule as $cgs ){

                        $pass      = true;
                        $cgs_start = $cgs->starts_at;
                        $cgs_end   = $cgs->getEndDateTime();

                        // no need to keep checking, they aren't chronologically near eachother
                        if( $cgs_start > $schedule_end ){

                            $pass = true;
                            break;
                        }

                        // if the schedules conflict, break
                        if( $schedule_start >= $cgs_start && $schedule_start <= $cgs_end || $schedule_end >= $cgs_start && $schedule_end <= $cgs_end ){

                            $pass = false;
                            break;
                        }
                    }

                    if( $pass ) return $schedule;
                })
                ->map( function( Schedule $schedule ) {

                    return [

                        'id'         => $schedule->id,
                        'start'      => $schedule->starts_at->copy()->format( \DateTime::ISO8601 ),
                        'client'     => $schedule->client->nameLastFirst(),
                        'client_id'  => $schedule->client->id,
                        'start_time' => $schedule->starts_at->copy()->format('g:i A'),
                        'end_time'   => $schedule->starts_at->copy()->addMinutes($schedule->duration)->addSecond()->format('g:i A')
                    ];
            });

            return [ 'events' => $schedules, 'requests' => $caregiver->mapped_schedule_requests ];
        }

        return view( 'open_shifts', [ 'businesses' => $caregiver->businesses, 'role_type' => auth()->user()->role_type ]);
    }
}
