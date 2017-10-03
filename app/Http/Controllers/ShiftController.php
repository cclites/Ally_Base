<?php

namespace App\Http\Controllers;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Shift;
use Illuminate\Http\Request;


class ShiftController extends Controller
{

    /**
     * @return \App\Caregiver
     */
    protected function caregiver()
    {
        return auth()->user()->role;
    }

    public function index()
    {
        if ($this->caregiver()->isClockedIn()) {
            return redirect()->route('clocked_in');
        }
        $events = $this->getRecentEvents()->toArray();
        return view('caregivers.clock_in', compact('events'));
    }

    public function clockedIn()
    {
        if (!$this->caregiver()->isClockedIn()) {
            return redirect()->route('shift.index');
        }
        $shift = $this->caregiver()->getActiveShift();
        return view('caregivers.clock_out', compact('shift'));
    }

    public function clockIn(Request $request)
    {
        if ($this->caregiver()->isClockedIn()) {
            return redirect()->route('clocked_in')->with('error', 'You are already clocked in.');
        }

        $request->validate([
            'schedule_id' => 'exists:schedules,id',
        ]);

        $schedule = Schedule::findOrFail($request->input('schedule_id'));

        $shift = new Shift([
            'client_id' => $schedule->client_id,
            'business_id' => $schedule->business_id,
            'checked_in_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'checked_in_latitude' => 39.9526, // needs to pull from request
            'checked_in_longitude' => 75.1652, // needs to pull from request
        ]);
        if ($this->caregiver()->shifts()->save($shift)) {
            \Session::flash('sucess', 'You have successfully clocked in.');
            return new SuccessResponse('You have successfully clocked in.');
            return redirect()->route('clocked_in')->with('status', 'You have successfully clocked in!');
        }
        return redirect()->back()->with('error', 'System Error: Unable to clock in.');
    }

    public function clockOut(Request $request)
    {
        if (!$this->caregiver()->isClockedIn()) {
            return new ErrorResponse(400, 'You are not currently clocked in.');
            return redirect()->route('shift.index');
        }

        $data = $request->validate([
            'caregiver_comments' => 'nullable',
            'mileage' => 'nullable|numeric',
            'other_expenses' => 'nullable|numeric'
        ]);

        $shift = $this->caregiver()->getActiveShift();
        $update = $shift->update(array_merge($data, [
            'checked_out_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'checked_out_latitude' => 39.9526, // needs to pull from request
            'checked_out_longitude' => 75.1652, // needs to pull from request
        ]));

        if ($update) {
            return new SuccessResponse('You have successfully clocked out.');
        }
        else {
            return new ErrorResponse(500, 'There was an error clocking out.');
        }
    }

    protected function getRecentEvents()
    {
        $aggregator = new ScheduleAggregator();
        foreach($this->caregiver()->schedules as $schedule) {
            $title = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $aggregator->add($title, $schedule);
        }

        $start = new \DateTime('-4 hours');
        $end = new \DateTime('+12 hours'); // determine if event's end time has passed in view

        $events = new ScheduleEventsResponse($aggregator->events($start, $end));
        return $events;
    }

}
