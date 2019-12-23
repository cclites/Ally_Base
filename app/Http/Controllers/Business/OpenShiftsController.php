<?php

namespace App\Http\Controllers\Business;

use App\Schedule;
use App\Scheduling\OpenShiftRequestStatus;
use Illuminate\Http\Request;

class OpenShiftsController extends BaseController
{
    public function index(Request $request)
    {
        if( !is_office_user() ) abort( 403 );

        $chain = $this->businessChain();

        if( request()->filled( 'json' ) ){

            $results = Schedule::forRequestedBusinesses()
                ->with([ 'client', 'schedule_requests' => function( $q ){

                    return $q->whereIn( 'status', [ OpenShiftRequestStatus::REQUEST_PENDING(), OpenShiftRequestStatus::REQUEST_UNINTERESTED() ] );
                }])
                ->ordered()
                ->inTheNextMonth( $chain->businesses->first()->timezone )
                ->whereOpen()
                ->get();


            $schedules = $results->map( function( Schedule $schedule ) {

                return [

                    'id'                => $schedule->id,
                    'start'             => $schedule->starts_at->copy()->format( \DateTime::ISO8601 ),
                    'client'            => $schedule->client->nameLastFirst(),
                    'client_id'         => $schedule->client->id,
                    'start_time'        => $schedule->starts_at->copy()->format('g:i A'),
                    'end_time'          => $schedule->starts_at->copy()->addMinutes( $schedule->duration )->addSecond()->format( 'g:i A' ),
                    'requests_count'    => $schedule->schedule_requests->filter( function( $r ){ return in_array( $r->status, [ OpenShiftRequestStatus::REQUEST_PENDING(), OpenShiftRequestStatus::REQUEST_UNINTERESTED() ]); })->count()
                ];
            });

            return [ 'events' => $schedules, 'requests' => [] ];
        }

        return view_component( 'open-shifts',
            'Open Shifts',
            [ 'businesses' => $chain->id, 'role_type' => auth()->user()->role_type ],
            [
                'Home' => route('home')
            ]
        );
    }
}
