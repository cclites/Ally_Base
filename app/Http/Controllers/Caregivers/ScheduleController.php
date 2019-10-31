<?php

namespace App\Http\Controllers\Caregivers;

use App\Business;
use App\Responses\ErrorResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;

class ScheduleController extends BaseController
{
    public function index()
    {
        return view('caregivers.schedule');
    }

    public function events(Request $request, ScheduleAggregator $aggregator)
    {
        $caregiver = auth()->user()->role;
        $aggregator->where('caregiver_id', $caregiver->id);

        $start = new Carbon(
            $request->input('start', date('Y-m-d', strtotime('First day of this month'))),
            $caregiver->businesses->first()->timezone ?? 'America/New_York'
        );
        $end = new Carbon(
            $request->input('end', date('Y-m-d', strtotime('First day of next month'))),
            $caregiver->businesses->first()->timezone ?? 'America/New_York'
        );

        $schedules = $aggregator->getSchedulesBetween($start, $end);

        $events = new ScheduleEventsResponse($schedules);
        return $events;
    }

    public function openShifts()
    {
        $caregiver = auth()->user()->role;

        if( request()->filled( 'json' ) ){

            // get business dynamically, needs to be plural because 'scopeForRequestedBusinesses' will pick it up
            $businessId = request()->input( 'businesses', null );

            if( empty( $businessId ) ) return new ErrorResponse( 500, 'Schedules could not be received' );

            $setting = Business::findOrFail( $businessId )->open_shifts_setting;

            if( !in_array( $setting, [ Business::OPEN_SHIFTS_LIMITED, Business::OPEN_SHIFTS_UNLIMITED ] ) ) return new ErrorResponse( 500, 'Invalid registry setting' );

            $query = Schedule::forRequestedBusinesses()
                ->with([ 'client' ])
                ->whereHas( 'client.rates', function ( $query ) use ( $setting, $caregiver ) {

                    if( $setting === Business::OPEN_SHIFTS_LIMITED ){

                        $query->where( 'caregiver_id', $caregiver->id );
                    }
                })
                ->withCount( 'schedule_requests' )
                ->ordered()
                ->whereDoesntHave( 'caregiver' )
                ->whereIn( 'status', [ Schedule::CAREGIVER_CANCELED, Schedule::OPEN_SHIFT, Schedule::OK ]);

            $start = Carbon::now();
            $end   = Carbon::parse( 'today +31 days' );

            $schedules = $query->whereBetween( 'starts_at', [ $start, $end ] )->get();

            $events = new ScheduleEventsResponse( $schedules );

            // dd( $events->toArray() );

            return [ 'events' => $events->toArray() ];
        }

        return view( 'open_shifts', [ 'businesses' => $caregiver->businesses, 'role_type' => auth()->user()->role_type ]);
    }
}
