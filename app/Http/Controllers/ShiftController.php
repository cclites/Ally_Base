<?php

namespace App\Http\Controllers;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\UnverifiedLocationException;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\SignedLTCI;
use App\Schedule;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Scheduling\ScheduleAggregator;
use App\Shifts\ClockIn;
use App\Shifts\ClockOut;
use App\Shift;
use App\ShiftIssue;
use App\Signature;
use Carbon\Carbon;
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

    public function index(ScheduleAggregator $aggregator, $schedule_id = null)
    {
        if ($this->caregiver()->isClockedIn()) {
            return redirect()->route('clocked_in');
        }
        $events = $this->getRecentEvents($aggregator)->toArray();
        return view('caregivers.clock_in', compact('events', 'schedule_id'));
    }

    public function clockedIn()
    {
        if (!$this->caregiver()->isClockedIn()) {
            return redirect()->route('shift.index');
        }
        $shift = $this->caregiver()->getActiveShift();
        $activities = $shift->business->allActivities();
        $carePlanActivityIds = [];
        $notes =  '';
        if ($shift && $shift->schedule) {
            $shift->load('client');
            $notes = $shift->schedule->notes;
            if ($shift->schedule->carePlan) {
                $carePlanActivityIds = $shift->schedule->carePlan->activities->pluck('id')->toArray();
            }
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

        $data['mileage'] = request('mileage', 0);
        $data['other_expenses'] = request('other_expenses', 0);

        // Get active shift
        $shift = $this->caregiver()->getActiveShift();
        if (!$shift || !$shift->client) {
            return new ErrorResponse(400, 'Could not find an active shift.');
        }

        /* Signature Requirement Disabled For Now
        // LTCI Clients Required Signature
        $request->validate([
            'signature' => [new SignedLTCI($shift->client->client_type)]
        ]);
        */

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
                Signature::onModelInstance($shift, request('signature'));
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

    protected function getRecentEvents(ScheduleAggregator $aggregator)
    {
        $start = new Carbon('-12 hours');
        $end = new Carbon('+12 hours');
        $schedules = $aggregator->where('caregiver_id', $this->caregiver()->id)
                                ->getSchedulesBetween($start, $end)
                                ->load('carePlan');

        $events = new ScheduleEventsResponse($schedules);
        return $events;
    }

    /**
     * Returns single shift details for report details modal.
     *
     * @param Shift $shift
     * @return void
     */
    public function shift(Shift $shift)
    {
        if ($shift->caregiver_id != $this->caregiver()->id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Load needed relationships
        $shift->load(['activities', 'issues', 'schedule', 'client', 'signature', 'statusHistory']);

        // Load shift data into array before loading client info
        $data = $shift->toArray();

        // Calculate distances
        $checked_in_distance = null;
        $checked_out_distance = null;
        if ($address = $shift->client->evvAddress) {
            if ($shift->checked_in_latitude || $shift->checked_in_longitude) {
                $checked_in_distance = $address->distanceTo($shift->checked_in_latitude, $shift->checked_in_longitude);
            }
            if ($shift->checked_out_latitude || $shift->checked_out_longitude) {
                $checked_out_distance = $address->distanceTo($shift->checked_out_latitude, $shift->checked_out_longitude);
            }
        }

        $data += [
            'checked_in_distance' => $checked_in_distance,
            'checked_out_distance' => $checked_out_distance,
            'client_name' => $shift->client->name(),
            'caregiver_name' => $shift->caregiver->name(),
        ];

        return response()->json($data);
    }
}
