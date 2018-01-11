<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends BaseController
{

    public function index()
    {
        return view('business.schedule', ['business' => $this->business()]);
    }

    public function events(Request $request, ScheduleAggregator $aggregator)
    {
        $aggregator->where('business_id', $this->business()->id);

        // Filter by client or caregiver
        if ($client_id = $request->input('client_id')) {
            $aggregator->where('client_id', $client_id);
        }
        if ($caregiver_id = $request->input('caregiver_id')) {
            $aggregator->where('caregiver_id', $caregiver_id);
        } elseif ($request->input('caregiver_id') === "0") {
            $aggregator->where('caregiver_id', null);
        }

        $start = new Carbon(
            $request->input('start', date('Y-m-d', strtotime('First day of this month'))),
            $this->business()->timezone
        );
        $end = new Carbon(
            $request->input('end', date('Y-m-d', strtotime('First day of next month'))),
            $this->business()->timezone
        );

        $events = new ScheduleEventsResponse($aggregator->getSchedulesBetween($start, $end));
        $events->setTitleCallback(function(Schedule $schedule) {
            $clientName = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $caregiverName = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            return $clientName . ' (' . $caregiverName . ')';
        });
        return $events;
    }

    /**
     * Retrieve the details of a schedule
     *
     * @param $client_id
     * @param $schedule_id
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function show($schedule_id)
    {
        $schedule = Schedule::findOrFail($schedule_id);

        if ($schedule->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

        return new ScheduleResponse($schedule);
    }

    public function print(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);
        $request->start = Carbon::parse($request->start_date);
        $request->end = Carbon::parse($request->end_date);
        $events = collect($this->events($request)->events)->map(function ($event) {
            $event["date"] = $event['start']->format('m/d/y');
            return $event;
        });
        return view('business.schedule_print', compact('events'));
    }

}
