<?php

namespace App\Http\Controllers;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\UnverifiedLocationException;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Scheduling\ClockIn;
use App\Scheduling\ClockOut;
use App\Shift;
use App\ShiftIssue;
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

    public function index($schedule_id = null)
    {
        if ($this->caregiver()->isClockedIn()) {
            return redirect()->route('clocked_in');
        }
        $events = $this->getRecentEvents()->toArray();
        return view('caregivers.clock_in', compact('events', 'schedule_id'));
    }

    public function clockedIn()
    {
        if (!$this->caregiver()->isClockedIn()) {
            return redirect()->route('shift.index');
        }
        $shift = $this->caregiver()->getActiveShift();
        $activities = $shift->business->allActivities();
        $notes = $shift->schedule->notes;
        $carePlanActivityIds = [];
        if ($shift->schedule->carePlan) {
            $carePlanActivityIds = $shift->schedule->carePlan->activities->pluck('id')->toArray();
        }
        return view('caregivers.clock_out', compact('shift', 'activities', 'notes', 'carePlanActivityIds'));
    }

    public function clockIn(Request $request)
    {
        if ($this->caregiver()->isClockedIn()) {
            return redirect()->route('clocked_in')->with('error', 'You are already clocked in.');
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
            'schedule_id' => 'exists:schedules,id',
            'latitude' => 'numeric|nullable|required_unless:manual,1',
            'longitude' => 'numeric|nullable|required_unless:manual,1',
            'manual' => 'nullable',
        ], [
            'schedule_id.exists' => 'You must select a valid shift from the drop down.',
            'latitude.required_unless' => 'Location services must be turned on or you must manually clock in.',
            'longitude.required_unless' => 'Location services must be turned on or you must manually clock in.',
        ]);

        try {
            $schedule = Schedule::find($request->input('schedule_id'));
            $clockIn = new ClockIn($this->caregiver());
            if (!empty($data['manual'])) $clockIn->setManual();
            $clockIn->setGeocode($data['latitude'] ?? null ,$data['longitude'] ?? null);
            $shift = $clockIn->clockIn($schedule);
            if ($shift) {
                return new SuccessResponse('You have successfully clocked in.');
            }
            return new ErrorResponse(500, 'System error clocking in.  Please refresh and try again.');
        }
        catch (UnverifiedLocationException $e) {
            return new ErrorResponse(400, $e->getMessage() . ' You will need to manually clock in.');
        }
        catch (InvalidScheduleParameters $e) {
            return new ErrorResponse(400, $e->getMessage());
        }
    }

    public function clockOut(Request $request)
    {
        if (!$this->caregiver()->isClockedIn()) {
            return new ErrorResponse(400, 'You are not currently clocked in.');
            return redirect()->route('shift.index');
        }

        $data = $request->validate([
            'caregiver_comments' => 'nullable',
            'mileage' => 'nullable|numeric|max:1000|min:0',
            'other_expenses' => 'nullable|numeric|max:1000|min:0',
            'other_expenses_desc' => 'nullable',
            'latitude' => 'numeric|nullable|required_unless:manual,1',
            'longitude' => 'numeric|nullable|required_unless:manual,1',
            'manual' => 'nullable',
        ], [
            'latitude.required_unless' => 'Location services must be turned on or you must manually clock out.',
            'longitude.required_unless' => 'Location services must be turned on or you must manually clock out.',
        ]);

        // Get active shift
        $shift = $this->caregiver()->getActiveShift();
        if (!$shift || !$shift->client) {
            return new ErrorResponse(400, 'Could not find an active shift.');
        }

        // If not private pay, ADL and comments are required
        if ($shift->client->client_type != 'private_pay') {
            $request->validate(
                [
                    'caregiver_comments' => 'required',
                    'activities' => 'min:1',
                ],
                [
                    'caregiver_comments.required' => 'Care notes are required for this client.',
                    'activities.min' => 'A minimum of one activity is required for this client.',
                ]
            );
        }

        try {
            $clockOut = new ClockOut($this->caregiver());
            if (!empty($data['manual'])) $clockOut->setManual();
            if ($data['other_expenses']) $clockOut->setOtherExpenses($data['other_expenses'], $data['other_expenses_desc']);
            if ($data['mileage']) $clockOut->setMileage($data['mileage']);
            if ($data['caregiver_comments']) $clockOut->setComments($data['caregiver_comments']);
            $clockOut->setGeocode($data['latitude'] ?? null ,$data['longitude'] ?? null);
            if ($clockOut->clockOut($shift, $request->input('activities', []))) {
                // Attach issues
                $issueText = trim($request->input('issue_text'));
                if ($request->input('caregiver_injury') || $issueText) {
                    $issue = new ShiftIssue([
                        'caregiver_injury' => $request->input('caregiver_injury'),
                        'comments' => $issueText,
                    ]);
                    $shift->issues()->save($issue);
                }
                return new SuccessResponse('You have successfully clocked out.');
            }
            return new ErrorResponse(500, 'System error clocking out.  Please refresh and try again.');
        }
        catch (UnverifiedLocationException $e) {
            return new ErrorResponse(400, $e->getMessage() . ' You will need to manually clock in.');
        }
        catch (InvalidScheduleParameters $e) {
            return new ErrorResponse(400, $e->getMessage());
        }
    }

    protected function getRecentEvents()
    {
        $start = new \DateTime('-12 hours');
        $end = new \DateTime('+12 hours');

        $events = new ScheduleEventsResponse($this->caregiver()->getEvents($start, $end));
        return $events;
    }
}
