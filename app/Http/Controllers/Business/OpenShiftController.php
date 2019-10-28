<?php

namespace App\Http\Controllers\Business;

use App\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;


class OpenShiftController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if( request()->filled( 'json' ) ){


            $query = Schedule::forRequestedBusinesses()
                ->with(['client', 'caregiver', 'shifts', 'services', 'service', 'carePlan', 'services.service'])
                ->ordered();

            $start = Carbon::now();
            $end   = Carbon::parse( 'today +31 days' );

            $schedules = $query->whereBetween( 'starts_at', [ $start, $end ] )->get();

            $events = new ScheduleEventsResponse( $schedules );

            return $events;
        }
        return view( 'open_shifts' );
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
