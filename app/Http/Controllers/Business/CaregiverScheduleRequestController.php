<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\CaregiverScheduleRequest;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;

class CaregiverScheduleRequestController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request )
    {

        $business = Business::findOrFail( $request->business_id );

        if( $request->input( 'count', false ) ){

            $count = CaregiverScheduleRequest::forOpenSchedules()
                ->wherePending()
                ->forSchedulesInTheNextMonth( $business->timezone )
                ->where( 'business_id', $business->id )
                ->count();

            return response()->json( compact( 'count' ) );
        }
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
