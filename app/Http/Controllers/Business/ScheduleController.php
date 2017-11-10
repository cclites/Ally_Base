<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use Illuminate\Http\Request;

class ScheduleController extends BaseController
{

    public function index()
    {
        return view('business.schedule');
    }

    public function events(Request $request)
    {
        $schedules = $this->business()->schedules;

        // Filter by client or caregiver
        if ($request->has('caregiver_id') || $request->has('client_id')) {
            $schedules = $schedules->filter(function(Schedule $schedule) use ($request) {
                if ($client_id = $request->input('client_id')) {
                    if ($schedule->client_id != $client_id) return false;
                }
                if ($caregiver_id = $request->input('caregiver_id')) {
                    if ($schedule->caregiver_id != $caregiver_id) return false;
                }
                else if ($request->input('caregiver_id') === "0") {
                    // Unassigned shifts only
                    if ($schedule->caregiver_id) return false;
                }
                return true;
            });
        }

        $aggregator = new ScheduleAggregator();
        foreach($schedules as $schedule) {
            $clientName = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $caregiverName = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            $title = $clientName . ' (' . $caregiverName . ')';
            $aggregator->add($title, $schedule);
        }

        $activeSchedules = $this->business()->shifts()->whereNull('checked_out_time')->pluck('schedule_id')->toArray();
        $aggregator->addActiveSchedules($activeSchedules);

        $start = $request->input('start', date('Y-m-d', strtotime('First day of last month -2 months')));
        $end = $request->input('end', date('Y-m-d', strtotime('First day of this month +13 months')));

        if (strlen($start) > 10) $start = substr($start, 0, 10);
        if (strlen($end) > 10) $end = substr($end, 0, 10);

        $events = new ScheduleEventsResponse($aggregator->events($start, $end));
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

}
