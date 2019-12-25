<?php

namespace App\Http\Controllers\Caregivers;

use App\Business;
use App\Responses\ErrorResponse;
use App\Schedule;

class OpenShiftsController extends BaseController
{
    public function index()
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

            $schedules = $query->get()
                ->filter( function( Schedule $schedule ) use( $caregivers_schedule ){

                    $schedule_start = $schedule->starts_at;
                    $schedule_end   = $schedule->getEndDateTime();
                    $pass           = true;
                    foreach( $caregivers_schedule as $cgs ){

                        $cgs_start = $cgs->starts_at;
                        $cgs_end   = $cgs->getEndDateTime();

                        // no need to keep checking, they aren't chronologically near eachother
                        if( $cgs_start > $schedule_end ) break;

                        // if the schedules conflict, break and filter out
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

            return [ 'events' => $schedules, 'requests' => $caregiver->schedule_requests ];
        }

        return view_component( 'open-shifts',
            'Open Shifts',
            [ 'businesses' => $caregiver->businesses, 'role_type' => auth()->user()->role_type ],
            [
                'Home' => route('home')
            ]
        );
    }
}
