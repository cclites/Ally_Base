<?php

namespace App\Http\Controllers;

use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Shift;
use Illuminate\Http\Request;


class ShiftController extends Controller
{
    const MAXIMUM_DISTANCE_METERS = 100;

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
        $activities = $shift->business->activities->sortBy('code');
        return view('caregivers.clock_out', compact('shift', 'activities'));
    }

    public function clockIn(Request $request)
    {
        if ($this->caregiver()->isClockedIn()) {
            return redirect()->route('clocked_in')->with('error', 'You are already clocked in.');
        }

        $data = $request->validate([
            'schedule_id' => 'exists:schedules,id',
            'latitude' => 'numeric|required_unless:manual,1',
            'longitude' => 'numeric|required_unless:manual,1',
            'manual' => 'nullable',
        ]);

        $schedule = Schedule::findOrFail($request->input('schedule_id'));

        $client = Client::find($schedule->client_id);
        if (!$client) return new ErrorResponse(400, 'Schedule does not have a client assigned to it.');

        $manual = !empty($data['manual']);
        if (!$manual) {
            if (!$client->evvAddress) return new ErrorResponse(400, 'Client does not have a service (EVV) address.  You will need to manually clock in.');
            if ($client->evvAddress->distanceTo($data['latitude'], $data['longitude'], 'm') > self::MAXIMUM_DISTANCE_METERS) {
                return new ErrorResponse(400, 'Your location does not match the service address.  You will need to manually clock in.');
            }
        }

        $shift = new Shift([
            'client_id' => $schedule->client_id,
            'business_id' => $schedule->business_id,
            'schedule_id' => $schedule->id,
            'checked_in_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'checked_in_latitude' => $data['latitude'], // needs to pull from request
            'checked_in_longitude' => $data['longitude'], // needs to pull from request
            'verified' => !$manual
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
            'other_expenses' => 'nullable|numeric',
            'latitude' => 'numeric|required_unless:manual,1',
            'longitude' => 'numeric|required_unless:manual,1',
            'manual' => 'nullable',
        ]);

        $shift = $this->caregiver()->getActiveShift();
        $client = $shift->client;
        if (!$shift || !$client) {
            return new ErrorResponse(400, 'Could not find an active shift.');
        }

        $manual = !empty($data['manual']);
        if (!$manual) {
            if (!$client->evvAddress) return new ErrorResponse(400, 'Client does not have a service (EVV) address.  You will need to manually clock out.');
            if ($client->evvAddress->distanceTo($data['latitude'], $data['longitude'], 'm') > self::MAXIMUM_DISTANCE_METERS) {
                return new ErrorResponse(400, 'Your location does not match the service address.  You will need to manually clock out.');
            }
        }

        $update = $shift->update(array_merge($data, [
            'checked_out_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'checked_out_latitude' => $data['latitude'], // needs to pull from request
            'checked_out_longitude' => $data['longitude'], // needs to pull from request
            'verified' => ($shift->verified && !$manual), // both check in and check out must have used EVV to be verified
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


        $start = new \DateTime('-8 hours');
        $end = new \DateTime('+12 hours'); // determine if event's end time has passed in view

        $events = new ScheduleEventsResponse($aggregator->events($start, $end));
        return $events;
    }
}
