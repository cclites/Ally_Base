<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\Billing\ClientRate;
use App\CaregiverScheduleRequest;
use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
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

            $business = Business::findOrFail( $request->business_id );

            $count = CaregiverScheduleRequest::forOpenSchedules()
                ->wherePending()
                ->forSchedulesInTheNextMonth( $business->timezone )
                ->where( 'business_id', $business->id )
                ->count();

            return response()->json( compact( 'count' ) );
        }

        $schedule = Schedule::with([ 'services', 'client', 'schedule_requests' => function( $q ){

            return $q->where( 'status', 'pending' );
        }])->findOrFail( $request->schedule );
        $this->authorize( 'read', $schedule );

        $schedule[ 'start' ]      = $schedule->starts_at->copy()->format( \DateTime::ISO8601 );
        $schedule[ 'start_time' ] = $schedule->starts_at->copy()->format( 'g:i A' );
        $schedule[ 'end_time' ]   = $schedule->starts_at->copy()->addMinutes( $schedule->duration )->addSecond()->format( 'g:i A' );

        $requests = $schedule->schedule_requests->map( function( $r ){

            $req = CaregiverScheduleRequest::find( $r->pivot->id ); // this may not be necessary, I may be able to pass this event in from eagar loading it above with the schedule..
            $r[ 'caregiver_client_relationship_exists' ] = $req->caregiver_client_relationship_exists();
            return $r;
        });

        return new SuccessResponse( 'Successfully loaded schedule requests..', [ 'requests' => $schedule->schedule_requests, 'schedule' => $schedule ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CaregiverScheduleRequest  $caregiverScheduleRequest
     * @return \Illuminate\Http\Response
     */
    public function show(CaregiverScheduleRequest $caregiverScheduleRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CaregiverScheduleRequest  $caregiverScheduleRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(CaregiverScheduleRequest $caregiverScheduleRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CaregiverScheduleRequest  $caregiverScheduleRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CaregiverScheduleRequest $caregiverScheduleRequest, ScheduleAggregator $aggregator)
    {
        // validate request object

        $schedule = Schedule::findOrFail( $request->schedule_id );

        $this->authorize( 'update', $schedule );

        $action = $request->status;

        DB::beginTransaction();
        switch( $action ){

            case 'approved':

                $newStatus = CaregiverScheduleRequest::REQUEST_APPROVED;
                if( !$caregiverScheduleRequest->update([ 'status' => $newStatus ]) ) return new ErrorResponse( 500, 'failed to update schedule request, please try again later' );

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
                $totalHours    = $aggregator->getTotalScheduledHoursForWeekOf( $schedule->starts_at, $schedule->client_id );
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

                // ERIK TODO => text them? notification? Ask Jason
                break;
            case 'denied':

                $newStatus = CaregiverScheduleRequest::REQUEST_DENIED;
                if( !$caregiverScheduleRequest->update([ 'status' => $newStatus ]) ) return new ErrorResponse( 500, 'failed to update schedule request, please try again later' );

                // Update the schedule
                if( !empty( $schedule->caregiver_id ) ){

                    $schedule->update([

                        'caregiver_id' => null
                    ]);
                }

                // ERIK TODO => text them? notification? Ask Jason
                break;
            default:

                return new ErrorResponse( 500, 'You do not have permission to perform that request.' );
                break;
        }

        DB::commit();

        return new SuccessResponse( 'Successfully updated schedule request!', $newStatus );
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CaregiverScheduleRequest  $caregiverScheduleRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(CaregiverScheduleRequest $caregiverScheduleRequest)
    {
        //
    }
}
