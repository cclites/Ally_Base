<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;

class ScheduleController extends BaseController
{
    public function index(Request $request)
    {
        return view_component('client-schedule', 'Scheduled Shifts', [], []);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function schedule(Request $request, Client $client){
        $startDate = Carbon::parse($request->input('start'), $client->getTimezone())->setTimezone('UTC')->toDateTimeString();
        $endDate = Carbon::parse($request->input('end'), $client->getTimezone())->setTimezone('UTC')->toDateTimeString();

        return $client->schedules()->with('caregiver')
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->get()
            ->map(function(Schedule $schedule){
            return [
                'schedule_id' => $schedule->id,
                'title'       => $schedule->caregiver_id ? optional($schedule->caregiver)->name : 'No Caregiver Assigned',
                'start'       => $schedule->starts_at->format('c'),
                'end'         => $schedule->starts_at->copy()->addMinutes($schedule->duration)->format('c'),
                'duration'    => $schedule->duration,
                'checked_in'  => $schedule->isClockedIn(),
                'client_id'   => $schedule->client_id,
                'caregiver_id'=> $schedule->caregiver_id,
                'client_name' => optional($schedule->client)->name,
                'caregiver_name' => $schedule->caregiver_id ? optional($schedule->caregiver)->name : 'No Caregiver Assigned',
                'caregiver_phones' => optional($schedule->caregiver)->phoneNumbers,
            ];
        });



    }

}
