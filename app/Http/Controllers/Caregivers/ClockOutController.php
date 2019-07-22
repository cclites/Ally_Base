<?php

namespace App\Http\Controllers\Caregivers;

use App\ClientType;
use App\Exceptions\InvalidScheduleParameters;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Shifts\ClockOut;
use App\Shift;
use App\ShiftIssue;
use App\Signature;
use Illuminate\Http\Request;
use App\Events\ShiftFlagsCouldChange;

class ClockOutController extends BaseController
{
    public function index()
    {
        if (!$this->caregiver()->isClockedIn()) {
            return redirect()->route('home');
        }

        // Get the active shifts
        $shifts = $this->caregiver()->getActiveShifts();

        if (sizeof($shifts) === 1) {
            return $this->show($shifts[0]);
        } else {
            return view('caregivers.shifts', compact('shifts'));
        }
    }

    public function show(Shift $shift)
    {
        $this->authorize('read', $shift);

        // Load the client relationship
        $shift->load('client');

        // Load the business model because we need the settings
        $business = $shift->business;

        // Load the available activities
        $activities = $business->allActivities();

        // load questions related to the current client
        $questions = $business->questions()->forType($shift->client->client_type)->get();

        // load tracked goals
        $goals = $shift->client->goals()->tracked()->get();

        // Load care plan and notes from the schedule (if one exists)
        $carePlanActivityIds = [];
        $notes = '';
        if ($shift && $shift->schedule) {
            $notes = $shift->schedule->notes;
            if ($shift->schedule->carePlan) {
                $carePlanActivityIds = $shift->schedule->carePlan->activities->pluck('id')->toArray();
            }
        }

        return view('caregivers.clock_out', compact(
            'shift',
            'activities',
            'notes',
            'carePlanActivityIds',
            'business',
            'questions',
            'goals'
        ));
    }

    public function clockOut(Request $request, Shift $shift)
    {
        if (auth()->user()->active == 0) {
            abort(403);
        }
        $this->authorize('read', $shift);

        if (!$shift || !$shift->client) {
            return new ErrorResponse(400, 'Could not find an active shift.');
        }

        if ($shift->checked_out_time) {
            return new ErrorResponse(400, "This shift has already been clocked out of.");
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


        if ($shift->business->require_signatures) {
            $request->validate([
                'signature' => 'required'
            ]);
        }

        // If not private pay, ADL and comments are required
        if ($shift->client->client_type != ClientType::PRIVATE_PAY) {
            $request->validate(
                [
                    'activities' => 'min:1',
                ],
                [
                    'activities.min' => 'A minimum of one activity is required for this client.',
                ]
            );
        }

        $allQuestions = $shift->business->questions()->forType($shift->client->client_type)->get();
        if ($allQuestions->count() > 0) {
            $fields = [];
            foreach ($allQuestions as $q) {
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
            $clockOut->setGeocode($data['latitude'] ?? null, $data['longitude'] ?? null);
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
                event(new ShiftFlagsCouldChange($shift));
                return new SuccessResponse('You have successfully clocked out.');
            }
            return new ErrorResponse(500, 'System error clocking out.  Please refresh and try again.');
        } catch (InvalidScheduleParameters $e) {
            return new ErrorResponse(400, $e->getMessage());
        }
    }
}
