<?php

namespace App\Http\Controllers;

use App\Scheduling\ScheduleAggregator;
use Illuminate\Http\Request;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;

class ScheduleController extends Controller
{
    public function index()
    {
        return view('caregivers.schedule');
    }

    public function events(Request $request)
    {
        $caregiver = auth()->user()->role;
        $aggregator = new ScheduleAggregator();
        foreach($caregiver->schedules as $schedule) {
            $title = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $aggregator->add($title, $schedule);
        }

        $start = $request->input('start', date('Y-m-d', strtotime('First day of last month -2 months')));
        $end = $request->input('end', date('Y-m-d', strtotime('First day of this month +13 months')));

        if (strlen($start) > 10) $start = substr($start, 0, 10);
        if (strlen($end) > 10) $end = substr($end, 0, 10);

        $events = new ScheduleEventsResponse($aggregator->events($start, $end));
        return $events;
    }
}
