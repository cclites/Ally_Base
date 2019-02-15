<?php

namespace App\Http\Controllers\Caregivers;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Shifts\ClockIn;
use Illuminate\Http\Request;

class ClockInController extends BaseController
{
    public function index(Schedule $schedule = null)
    {
        return view('caregivers.clock_in', compact('schedule'));
    }

    public function clockedIn()
    {
        if (!$this->caregiver()->isClockedIn()) {
            return redirect()->route('shift.index');
        }

        // Get the active shift
        $shift = $this->caregiver()->getActiveShift();

        // Load the client relationship
        $shift->load('client');

        // Load the business model because we need the settings
        $business = $shift->business;

        // Load the available activities
        $activities = $business->allActivities();

        // Load care plan and notes from the schedule (if one exists)
        $carePlanActivityIds = [];
        $carePlan = null;
        $schedule = null;
        if ($shift && $shift->schedule) {
            if ($shift->schedule->carePlan) {
                $carePlan = $shift->schedule->carePlan;
                $carePlanActivityIds = $shift->schedule->carePlan->activities->pluck('id')->toArray();
            }
        }

        return view('caregivers.clocked_in', compact('shift', 'schedule', 'carePlan', 'carePlanActivityIds', 'activities'));
    }

    public function clockIn(Request $request)
    {
        if (auth()->user()->active == 0) {
            abort(403);
        }

        if ($this->caregiver()->isClockedIn($request->input('client_id'))) {
            return new ErrorResponse(500, 'You are already clocked in for this client.');
            // return redirect()->route('clocked_in')->with('error', 'You are already clocked in.');
        }

        if ($request->input('debugMode')) {
            $schedule = Schedule::find($request->input('schedule_id'));
            $address = ($schedule) ? $schedule->client->evvAddress : null;
            $geocode = ($address) ? $address->getGeocode() : null;

            return [
                'stats' => [
                    [
                        'key' => 'evv_latitude',
                        'value' => ($geocode) ? $geocode->latitude : null,
                    ],
                    [
                        'key' => 'evv_longitude',
                        'value' => ($geocode) ? $geocode->longitude : null,
                    ],
                    [
                        'key' => 'your_latitude',
                        'value' => $request->input('latitude'),
                    ],
                    [
                        'key' => 'your_longitude',
                        'value' => $request->input('longitude'),
                    ],
                    [
                        'key' => 'distance_meters',
                        'value' => ($geocode) ? $geocode->distanceTo($request->input('latitude'), $request->input('longitude'), 'm') : null,
                    ],
                ]
            ];
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'latitude' => 'numeric|nullable',
            'longitude' => 'numeric|nullable',
        ], [
            'schedule_id.exists' => 'The selected shift is unavailable.  Please refresh and try again.',
            'client_id.exists' => 'The selected client is unavailable.  Please refresh and try again.',
        ]);

        try {
            $clockIn = new ClockIn($this->caregiver());
            $clockIn->setGeocode($data['latitude'] ?? null, $data['longitude'] ?? null);
            $shift = $this->completeClockIn($clockIn, $request->input('schedule_id'), $request->input('client_id'));
            if ($shift) {
                return new SuccessResponse('You have successfully clocked in.');
            }
            return new ErrorResponse(500, 'System error clocking in.  Please refresh and try again.');
        } catch (InvalidScheduleParameters $e) {
            return new ErrorResponse(400, $e->getMessage());
        }
    }

    /**
     * Complete the clock in depending on whether a schedule is provided
     *
     * @param \App\Shifts\ClockIn $clockIn
     * @param int|null $scheduleId
     * @param int|null $clientId
     * @return \App\Shift|bool|false
     * @throws \App\Exceptions\InvalidScheduleParameters
     * @throws \App\Exceptions\UnverifiedLocationException
     */
    protected function completeClockIn(ClockIn $clockIn, $scheduleId = null, $clientId = null)
    {
        if ($scheduleId && $schedule = Schedule::find($scheduleId)) {
            return $clockIn->clockIn($schedule);
        }
        if ($clientId && $client = Client::find($clientId)) {
            return $clockIn->clockInWithoutSchedule($client);
        }
        throw new \Exception('ShiftController: Missing client or schedule to clock into.');
    }
}
