<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\Billing\ClientRate;
use App\Caregiver;
use App\CaregiverScheduleRequest;
use App\Client;
use App\Notifications\Business\OpenShiftRequestAccepted;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Scheduling\OpenShiftRequestStatus;
use App\Scheduling\ScheduleAggregator;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaregiverScheduleRequestController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request )
    {

        if( $request->input( 'count', false ) ){
            // maybe this can be organized better.. this is for the top-notification-icon

            $businesses = auth()->user()->role->businesses;

            $count = CaregiverScheduleRequest::forOpenSchedules()
                ->whereActive()
                ->forSchedulesInTheNextMonth( $businesses->first()->timezone )
                ->whereIn( 'business_id', $businesses->pluck( 'id' )->toArray() )
                ->count();

            return response()->json( compact( 'count' ) );
        }

        $schedule = Schedule::with([ 'services', 'client', 'scheduleRequests' => function( $q ){

            return $q->whereActiveOrUninterested();
        }])->findOrFail( $request->schedule );
        $this->authorize( 'read', $schedule );

        $schedule[ 'start' ]      = $schedule->starts_at->copy()->format( \DateTime::ISO8601 );
        $schedule[ 'start_time' ] = $schedule->starts_at->copy()->format( 'g:i A' );
        $schedule[ 'end_time' ]   = $schedule->starts_at->copy()->addMinutes( $schedule->duration )->addSecond()->format( 'g:i A' );

        $requests = $schedule->scheduleRequests->map( function( $r ){

            $r[ 'caregiverClientRelationshipExists' ] = $r->caregiverClientRelationshipExists();
            $r[ 'nameLastFirst' ] = $r->caregiver->nameLastFirst;
            return $r;
        });

        return new SuccessResponse( 'Successfully loaded schedule requests..', [ 'requests' => $requests, 'schedule' => $schedule ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CaregiverScheduleRequest  $caregiverScheduleRequest
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, CaregiverScheduleRequest $caregiverScheduleRequest, Schedule $schedule )
    {
        $this->authorize( 'update', $schedule );

        $action = $request->status;

        DB::beginTransaction();
        switch( $action ){

            case OpenShiftRequestStatus::REQUEST_APPROVED():

                if( !$caregiverScheduleRequest->update([ 'status' => $action ]) ) return new ErrorResponse( 500, 'failed to update schedule request, please try again later' );

                $client = Client::find( $schedule->client_id );

                if ( $request->caregiver_id && !$client->hasCaregiver( $request->caregiver_id ) ) {

                    // Create default rates based on the rates in the request
                    ClientRate::add( $client, [

                        'caregiver_id'          => $request->caregiver_id,
                        'effective_start'       => date('Y') . '-01-01',
                        'effective_end'         => '9999-12-31',
                        'caregiver_hourly_rate' => 0,
                        'client_hourly_rate'    => 0,
                        'caregiver_fixed_rate'  => 0,
                        'client_fixed_rate'     => 0,
                        'service_id'            => $schedule->service_id,
                        'payer_id'              => $schedule->payer_id,
                    ]);

                    // Clear out the rates for all services so they are
                    // pulled from the defaults that were just created.
                    $request->caregiver_rate = null;
                    $request->client_rate    = null;
                }

                // Verify we are not going above hours
                $totalHours    = $this->getTotalScheduledHoursForWeekOf( $schedule->starts_at, $schedule->client_id );
                $newTotalHours = $totalHours - ($schedule->duration / 60);
                if ( $newTotalHours > $client->max_weekly_hours ) {

                    return new ErrorResponse( 500, 'The week of ' . $schedule->starts_at->toDateString() . ' exceeds the maximum allowed hours for this client.' );
                }

                // Update the schedule
                $schedule->update([

                    'caregiver_rate' => $request->caregiver_rate,
                    'client_rate'    => $request->client_rate,
                    'caregiver_id'   => $request->caregiver_id
                ]);

                $caregiverUser = User::find( $request->caregiver_id ); // just go ahead right to the user.. skip the extra eagar load query
                \Notification::send( $caregiverUser, new OpenShiftRequestAccepted( $schedule, $caregiverScheduleRequest->business ) );

                break;
            case OpenShiftRequestStatus::REQUEST_DENIED():

                if( !$caregiverScheduleRequest->update([ 'status' => $action ]) ) return new ErrorResponse( 500, 'failed to update schedule request, please try again later' );

                // Update the schedule
                if( !empty( $schedule->caregiver_id ) ){

                    $schedule->update([

                        'caregiver_id' => null
                    ]);
                }
                break;
            default:

                return new ErrorResponse( 500, 'You do not have permission to perform that request.' );
                break;
        }

        DB::commit();

        return new SuccessResponse( 'Successfully updated schedule request!', $action );
    }

    private function getTotalScheduledHoursForWeekOf( Carbon $date, $client_id )
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        $schedules = Schedule::where('client_id', $client_id)
            ->whereBetween( 'starts_at', [ $weekStart, $weekEnd ]);

        return $schedules->sum('duration') / 60;
    }
}
