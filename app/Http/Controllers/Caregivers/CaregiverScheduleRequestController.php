<?php

namespace App\Http\Controllers\Caregivers;

use App\CaregiverScheduleRequest;
use App\Responses\ErrorResponse;
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

        $caregiver = auth()->user()->role;

        // create model relationship for this.. replace all instances ( below here as well as in the event response too )
        $status = optional( $schedule->fresh()->latest_request_for( $caregiver->id ) )->status;

        switch( $status ){

            case null:
            case CaregiverScheduleRequest::REQUEST_CANCELLED:
                // create a pending

                $schedule->schedule_requests()->attach( $caregiver->id, [ 'status' => 'pending', 'business_id' => $schedule->business_id ]);
                break;
            case CaregiverScheduleRequest::REQUEST_PENDING:
            case CaregiverScheduleRequest::REQUEST_APPROVED:
                // create a cancelled

                $schedule->schedule_requests()->attach( $caregiver->id, [ 'status' => 'cancelled', 'business_id' => $schedule->business_id ]);
                // if approved, will also need to flag the schedule/shift as caregiver_cancelled
                break;
            default:
                // this is either invalid or denied.. which the caregiver shouldnt be able to do anything with

                return new ErrorResponse( 500, 'Unable to request shift at this time, please contact support' );
                break;
        }

        $status = $schedule->fresh()->latest_request_for( $caregiver->id )->status;

        return new SuccessResponse( "Schedule request updated to: " . $status, compact( 'status' ) );
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
