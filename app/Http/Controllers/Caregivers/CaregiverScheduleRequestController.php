<?php

namespace App\Http\Controllers\Caregivers;

use App\CaregiverScheduleRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Scheduling\OpenShiftRequestStatus;
use Illuminate\Http\Request;

class CaregiverScheduleRequestController extends BaseController
{
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
        if( !OpenShiftRequestStatus::isAcceptableStatus( $request->status ) ) new ErrorResponse( 500, 'Unable to request shift at this time, please contact support' );

        // if the schedule is not open anymore, dont allow the request to go through
        if( !$schedule->is_open ) new ErrorResponse( 500, 'Schedule is no longer open, please contact support or refresh your page.', [ 'code' => CaregiverScheduleRequest::ERROR_SCHEDULE_TAKEN_RACE_CONDITION ] );

        $caregiver = auth()->user()->role;

        $outstanding_request = $schedule->latestRequestFor( $caregiver->id );

        if( optional( $outstanding_request )->status == OpenShiftRequestStatus::REQUEST_DENIED() ) return new ErrorResponse( 500, 'Schedule is no longer open, please contact support or refresh your page.', [ 'code' => CaregiverScheduleRequest::ERROR_REQUEST_DENIED_AND_CAREGIVER_TRIED_AGAIN ] );

        if( empty( $outstanding_request ) ){
            // no existing relationship, create one

            $new_request = new CaregiverScheduleRequest();
            $new_request->schedule_id  = $schedule->id;
            $new_request->caregiver_id = $caregiver->id;
            $new_request->status       = $request->status;
            $new_request->business_id  = $schedule->business_id;
            $new_request->client_id    = $schedule->client_id;
            $new_request->save();
            return new SuccessResponse( "Schedule requested.", [ 'status' => $request->status ]);
        } else {

            $outstanding_request->update([ 'status' => $request->status ]);
            $outstanding_request->touch();
            return new SuccessResponse( "Schedule request updated", [ 'status' => $request->status ]);
        }
    }
}
