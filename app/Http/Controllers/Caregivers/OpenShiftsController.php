<?php

namespace App\Http\Controllers\Caregivers;

use App\BusinessChain;
use App\ClientExcludedCaregiver;
use App\Responses\ErrorResponse;
use App\Schedule;

class OpenShiftsController extends BaseController
{
    public function index()
    {
        $caregiver = auth()->user()->role;

        if( !auth()->user()->can( 'view-open-shifts' ) ) return new ErrorResponse( 403, 'Invalid registry setting' );

        if( request()->filled( 'json' ) ){

            $setting  = $caregiver->role->businessesChains()->first()->open_shifts_setting;
            $timezone = $caregiver->role->businesses->first()->timezone;

            $excluded = ClientExcludedCaregiver::where( 'caregiver_id', auth()->user()->id )->pluck( 'client_id' )->toArray();

            $query = Schedule::forRequestedBusinesses( $caregiver->role->businesses->pluck( 'id' )->toArray() )
                ->with([ 'client' ])
                ->whereNotIn( 'client_id', $excluded )
                ->inTheNextMonth( $timezone )
                ->whereOpen()
                ->ordered();

            if( $setting === BusinessChain::OPEN_SHIFTS_LIMITED ){
                // check to see if you should only grab related caregiver-clients
                $query->whereHas( 'client.rates', function ( $q ) use ( $caregiver ) {

                    $q->where( 'caregiver_id', $caregiver->id );
                });
            }

            // get all of the caregver's existing schedules in the same time frame
            $caregivers_schedule = Schedule::forRequestedBusinesses( $caregiver->role->businesses->pluck( 'id' )->toArray() )
                ->forCaregiver( $caregiver )
                ->inTheNextMonth( $timezone )
                ->ordered()
                ->get();

            $schedules = $query->get()
                ->filter( function( Schedule $schedule ) use ( $caregivers_schedule ){

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
                ->map( function( Schedule $schedule ) use ( $caregiver ){
                    // TODO => turn this into a resource and have it be used in the Business\OpenShiftsController as well

                    return [

                        'id'             => $schedule->id,
                        'start'          => $schedule->starts_at->copy()->format( \DateTime::ISO8601 ),
                        'client'         => $schedule->client->lastname,
                        'client_id'      => $schedule->client->id,
                        'start_time'     => $schedule->starts_at->copy()->format('g:i A'),
                        'distance'       => ( $schedule->client->evvAddress && $caregiver->address ) ? round_to_fraction( $caregiver->address->distanceToAddress( $schedule->client->evvAddress, 'mi' ) ) : null,
                        'end_time'       => $schedule->starts_at->copy()->addMinutes($schedule->duration)->addSecond()->format('g:i A'),
                        'requests_count' => null
                    ];
            });

            return [ 'events' => $schedules, 'requests' => $caregiver->scheduleRequests ];
        }
    }
}
