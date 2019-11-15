<?php

namespace App\Http\Controllers\Caregivers;

use App\CaregiverScheduleRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use Illuminate\Http\Request;

class CaregiverScheduleRequestController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store( Request $request, Schedule $schedule )
    {
        if( !is_caregiver() ) abort( 403 );

        // white list acceptable values
        if( !CaregiverScheduleRequest::is_acceptable_status( $request->status ) ) new ErrorResponse( 500, 'Unable to request shift at this time, please contact support' );

        // if the schedule is not open anymore, dont allow the request to go through
        if( !$schedule->is_open ) new ErrorResponse( 500, 'Schedule is no longer open, please contact support or refresh your page.', [ 'code' => CaregiverScheduleRequest::ERROR_SCHEDULE_TAKEN_RACE_CONDITION ] );

        $caregiver = auth()->user()->role;

        $outstanding_request = $schedule->latest_request_for( $caregiver->id );

        if( $outstanding_request->status == CaregiverScheduleRequest::REQUEST_DENIED ) return new ErrorResponse( 500, 'Schedule is no longer open, please contact support or refresh your page.', [ 'code' => CaregiverScheduleRequest::ERROR_REQUEST_DENIED_AND_CAREGIVER_TRIED_AGAIN ] );

        if( empty( $outstanding_request ) ){
            // no existing relationship, create one

            $schedule->schedule_requests()->attach( $caregiver->id, [ 'status' => $request->status, 'business_id' => $schedule->business_id, 'client_id' => $schedule->client_id ]);
            return new SuccessResponse( "Schedule requested.", [ 'status' => $request->status ]);
        } else {

            $outstanding_request->update([ 'status' => $request->status ]);
            $outstanding_request->touch();
            return new SuccessResponse( "Schedule request updated", [ 'status' => $request->status ]);
        }
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
    public function update(Request $request, CaregiverScheduleRequest $caregiverScheduleRequest)
    {
        //
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
