<?php

namespace App\Http\Controllers\Caregivers;

use App\Business;
use App\CaregiverScheduleRequest;
use App\Responses\ErrorResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;
use App\Responses\SuccessResponse;

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

    /**
     * I think this belongs in the schedule controller because an 'open-shift' is an attribute of a schedule, not its own entity..
     */
    public function openShifts()
    {
        $caregiver = auth()->user()->role;

        if( request()->filled( 'json' ) ){

            // get business dynamically, needs to be plural because 'scopeForRequestedBusinesses' will pick it up
            $businessId = request()->input( 'businesses', null );

            if( empty( $businessId ) ) return new ErrorResponse( 500, 'Schedules could not be received' );

            $business = Business::findOrFail( $businessId );
            $setting = $business->open_shifts_setting;

            if( !in_array( $setting, [ Business::OPEN_SHIFTS_LIMITED, Business::OPEN_SHIFTS_UNLIMITED ] ) ) return new ErrorResponse( 500, 'Invalid registry setting' );

            $query = Schedule::forRequestedBusinesses()
                ->with([ 'client' ])
                ->whereOpen()
                ->ordered();

            if( $setting === Business::OPEN_SHIFTS_LIMITED ){
                // check to see if you should only grab related caregiver-cients

                $query->whereHas( 'client.rates', function ( $q ) use ( $caregiver ) {

                    $q->where( 'caregiver_id', $caregiver->id );
                });
            }

            $schedules = $query->inTheNextMonth( $business->timezone )
                ->get()
                ->map( function( Schedule $schedule ) {

                return [

                    'id'         => $schedule->id,
                    'start'      => $schedule->starts_at->copy()->format( \DateTime::ISO8601 ),
                    'client'     => $schedule->client->nameLastFirst(),
                    'client_id'  => $schedule->client->id,
                    'start_time' => $schedule->starts_at->copy()->format('g:i A'),
                    'end_time'   => $schedule->starts_at->copy()->addMinutes($schedule->duration)->addSecond()->format('g:i A')
                ];
            });

            return [ 'events' => $schedules, 'requests' => $caregiver->mapped_schedule_requests ];
        }

        return view( 'open_shifts', [ 'businesses' => $caregiver->businesses, 'role_type' => auth()->user()->role_type ]);
    }
}
