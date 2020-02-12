<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Schedule;
use App\Scheduling\OpenShiftRequestStatus;
use Illuminate\Http\Request;

class OpenShiftsController extends BaseController
{
    public function index(Request $request)
    {
        if( !is_office_user() || !auth()->user()->can( 'view-open-shifts' ) ) return new ErrorResponse( 403, 'Invalid registry setting' );

        if( request()->filled( 'json' ) ){

            $chain = $this->businessChain();

            $results = Schedule::forRequestedBusinesses( auth()->user()->role->businesses->pluck( 'id' )->toArray() )
                ->whereHas( 'scheduleRequests', function( $q ){

                    return $q->whereActive();
                })
                ->with([ 'client', 'scheduleRequests' => function( $q ){

                    return $q->whereActiveOrUninterested();
                }])
                ->ordered()
                ->inTheNextMonth( $chain->businesses->first()->timezone )
                ->whereOpen()
                ->get();


            $schedules = $results->map( function( Schedule $schedule ) {
                // TODO => turn this into a resource and have it be used in the Business\OpenShiftsController as well

                return [

                    'id'                => $schedule->id,
                    'start'             => $schedule->starts_at->copy()->format( \DateTime::ISO8601 ),
                    'client'            => $schedule->client->nameLastFirst(),
                    'client_id'         => $schedule->client->id,
                    'start_time'        => $schedule->starts_at->copy()->format('g:i A'),
                    'distance'          => null,
                    'end_time'          => $schedule->starts_at->copy()->addMinutes( $schedule->duration )->addSecond()->format( 'g:i A' ),
                    'requests_count'    => $schedule->scheduleRequests->filter( function( $r ){ return in_array( $r->status, [ OpenShiftRequestStatus::REQUEST_PENDING() ]); })->count()
                ];
            });

            return [ 'events' => $schedules, 'requests' => [] ];
        }
    }
}
