<?php

namespace App\Http\Controllers\Caregivers;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\UnverifiedLocationException;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Shifts\ClockIn;
use App\Shifts\ClockOut;
use App\Shift;
use App\ShiftIssue;
use App\Signature;
use Illuminate\Http\Request;


class ShiftController extends BaseController
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
            $clockIn->setGeocode($data['latitude'] ?? null ,$data['longitude'] ?? null);
            $shift = $this->completeClockIn($clockIn, $request->input('schedule_id'), $request->input('client_id'));
            if ($shift) {
                return new SuccessResponse('You have successfully clocked in.');
            }
            return new ErrorResponse(500, 'System error clocking in.  Please refresh and try again.');
        }
        catch (UnverifiedLocationException $e) {
            // Create an unverified/manual shift
            $clockIn->setManual(true);
            $shift = $this->completeClockIn($clockIn, $request->input('schedule_id'), $request->input('client_id'));
            if ($shift) {
                return new SuccessResponse('You have successfully clocked in.');
            }
            return new ErrorResponse(500, 'System error clocking in.  Please refresh and try again.');
        }
        catch (InvalidScheduleParameters $e) {
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
            return $clockIn->clockInWithoutSchedule($client->business, $client);
        }
        throw new \Exception('ShiftController: Missing client or schedule to clock into.');
    }

    public function showClockOut()
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

        // load questions related to the current client
        $questions = $business->questions()->forType($shift->client->client_type)->get();

        // Load care plan and notes from the schedule (if one exists)
        $carePlanActivityIds = [];
        $notes =  '';
        if ($shift && $shift->schedule) {
            $notes = $shift->schedule->notes;
            if ($shift->schedule->carePlan) {
                $carePlanActivityIds = $shift->schedule->carePlan->activities->pluck('id')->toArray();
            }
        }

        return view('caregivers.clock_out', compact('shift', 'activities', 'notes', 'carePlanActivityIds', 'business', 'questions'));
    }

    public function clockOut(Request $request)
    {
        if (auth()->user()->active == 0) {
            abort(403);
        }
        
        if (!$this->caregiver()->isClockedIn()) {
            return new ErrorResponse(400, 'You are not currently clocked in.');
//            return redirect()->route('shift.index');
        }

        $data = $request->validate([
            'caregiver_comments' => 'nullable',
            'mileage' => 'nullable|numeric|max:1000|min:0',
            'other_expenses' => 'nullable|numeric|max:1000|min:0',
            'other_expenses_desc' => 'nullable',
            'latitude' => 'numeric|nullable',
            'longitude' => 'numeric|nullable',
            'goals' => 'nullable|array',
            'questions' => 'nullable|array',
            'narrative_notes' => 'nullable',
        ]);

        $data['mileage'] = request('mileage', 0);
        $data['other_expenses'] = request('other_expenses', 0);

        // Get active shift
        $shift = $this->caregiver()->getActiveShift();
        if (!$shift || !$shift->client) {
            return new ErrorResponse(400, 'Could not find an active shift.');
        }

        if ($shift->business->require_signatures) {
            $request->validate([
                'signature' => 'required'
            ]);
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

        $allQuestions = $shift->business->questions()->forType($shift->client->client_type)->get();
        if ($allQuestions->count() > 0) {
            $fields = [];
            foreach($allQuestions as $q) {
                if ($q->required == 1) {
                    $fields['questions.' . $q->id] = 'required';
                }
            }

            $request->validate($fields, ['questions.*' => 'Please answer all required questions.']);
        }

        try {
            $clockOut = new ClockOut($this->caregiver());
            if ($data['other_expenses']) $clockOut->setOtherExpenses($data['other_expenses'], $data['other_expenses_desc']);
            if ($data['mileage']) $clockOut->setMileage($data['mileage']);
            if ($data['caregiver_comments']) $clockOut->setComments($data['caregiver_comments']);
            $clockOut->setGoals($data['goals']);
            $clockOut->setQuestions($data['questions'], $allQuestions);
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
                if ($narrativeNotes = $request->input('narrative_notes')) {
                    $shift->client->narrative()->create(['notes' => $narrativeNotes, 'creator_id' => auth()->id()]);
                }
                return new SuccessResponse('You have successfully clocked out.');
            }
            return new ErrorResponse(500, 'System error clocking out.  Please refresh and try again.');
        }
        catch (UnverifiedLocationException $e) {
            $clockOut->setManual(true);
            if ($clockOut->clockOut($shift)) {
                if ($data['narrative_notes']) {
                    $shift->client->narrative()->create(['notes' => $data['narrative_notes'], 'creator_id' => auth()->id()]);
                }
                return new SuccessResponse('You have successfully clocked out.');
            }
            return new ErrorResponse(500, 'System error clocking out.  Please refresh and try again.');
        }
        catch (InvalidScheduleParameters $e) {
            return new ErrorResponse(400, $e->getMessage());
        }
    }

    /**
     * Returns single shift details for report details modal.
     *
     * @param Shift $shift
     * @return \Illuminate\Http\Response
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
