<?php

namespace App\Http\Controllers\Business;

use App\Events\ShiftModified;
use App\Events\UnverifiedShiftApproved;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Shift;
use App\ShiftIssue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends BaseController
{
    public function index()
    {
        // TODO: redirect to Shift Report
    }

    public function create()
    {
        $activities = $this->business()->allActivities();
        $caregivers = $this->business()->caregivers;
        $clients = $this->business()->clients;
        return view('business.shifts.create', compact('activities', 'caregivers', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'caregiver_comments' => 'nullable',
            'mileage' => 'nullable|numeric',
            'other_expenses' => 'nullable|numeric',
            'checked_in_time' => 'required|date',
            'checked_out_time' => 'required|date',
            'verified' => 'boolean',
            'caregiver_rate' => 'required|numeric',
            'provider_fee' => 'required|numeric',
            'hours_type' => 'required|in:default,overtime,holiday',
        ]);

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);
        $data['business_id'] = $this->business()->id;
        $data['status'] = Shift::WAITING_FOR_AUTHORIZATION;

        if ($shift = Shift::create($data)) {
            $shift->activities()->sync($request->input('activities', []));
            foreach($request->input('issues', []) as $issue) {
                $issue = new ShiftIssue([
                    'caregiver_injury' => $issue['caregiver_injury'] ?? 0,
                    'client_injury' => $issue['client_injury'] ?? 0,
                    'comments' => $issue['comments'] ?? ''
                ]);
                $shift->issues()->save($issue);
            }
            return new SuccessResponse('You have successfully created this shift.', ['id' => $shift->id], route('business.shifts.show', [$shift->id]));
        }

        return new ErrorResponse(500, 'Error creating the shift.');
    }

    public function show(Request $request, Shift $shift)
    {
        $shift->load(['activities', 'issues', 'schedule', 'client']);
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

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


        if ($request->expectsJson()) {
            $data += [
                'checked_in_distance' => $checked_in_distance,
                'checked_out_distance' => $checked_out_distance,
                'client_name' => $shift->client->name(),
                'caregiver_name' => $shift->caregiver->name(),
            ];

            return $data;
        }

        $activities = $shift->business->allActivities();
        $caregivers = $shift->business->caregivers;
        $clients = $shift->business->clients;

        return view('business.shifts.show', compact('shift', 'checked_in_distance', 'checked_out_distance', 'activities', 'clients', 'caregivers'));
    }

    public function update(Request $request, Shift $shift) {
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'caregiver_comments' => 'nullable',
            'mileage' => 'nullable|numeric',
            'other_expenses' => 'nullable|numeric',
            'checked_in_time' => 'required|date',
            'checked_out_time' => 'required|date',
            'verified' => 'boolean',
            'caregiver_rate' => 'required|numeric',
            'provider_fee' => 'required|numeric',
            'hours_type' => 'required|in:default,overtime,holiday',
        ]);

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);

        if (!empty($data['verified']) && $data['verified'] != $shift->verified) {
             event(new UnverifiedShiftApproved($shift));
        }

        if ($shift->update($data)) {
            $shift->activities()->sync($request->input('activities', []));
            event(new ShiftModified($shift));
            return new SuccessResponse('You have successfully updated this shift.');
        }
        return new ErrorResponse(500, 'The shift could not be updated.');
    }

    public function verify(Shift $shift)
    {
        $shift->load(['activities', 'issues']);
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if ($shift->update(['verified' => true])) {
            event(new UnverifiedShiftApproved($shift));
            event(new ShiftModified($shift));
            return new SuccessResponse('The shift has been verified');
        }

        return new ErrorResponse('The shift could not be verified');
    }

    public function storeIssue(Request $request, Shift $shift)
    {
        $shift->load(['activities', 'issues']);
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        $data = $request->validate([
            'caregiver_injury' => 'boolean',
            'client_injury' => 'boolean',
            'comments' => 'nullable',
        ]);

        $issue = new ShiftIssue($data);
        if ($shift->issues()->save($issue)) {
            return new CreatedResponse('The issue has been created successfully.', $issue->toArray());
        }
        return new ErrorResponse(500, 'Unable to create issue.');
    }

    public function updateIssue(Request $request, Shift $shift, $issue_id)
    {
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        $issue = $shift->issues()->where('id', $issue_id)->firstOrFail();
        $data = $request->validate([
            'caregiver_injury' => 'boolean',
            'client_injury' => 'boolean',
            'comments' => 'nullable',
        ]);

        if ($issue->update($data)) {
            return new SuccessResponse('The issue has been updated successfully.', $issue->toArray());
        }
        return new ErrorResponse(500, 'Unable to update issue.');
    }

    public function convertSchedule(Request $request, Schedule $schedule)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->input('date');

        // Make sure schedule has proper assignments
        if (!$schedule->caregiver_id) return new ErrorResponse(400,'There is no caregiver assigned to this scheduled shift, cannot convert.');
        if (!$schedule->client_id) return new ErrorResponse(400,'There is no client assigned to this scheduled shift, cannot convert.');

        $searchStart = (new Carbon($date, $this->business()->timezone))->setTime(0, 0, 0);
        $searchEnd = $searchStart->copy()->addDay();
        $occurrences = $schedule->getOccurrencesBetween($searchStart, $searchEnd);

        // Make sure 1 occurrence was found
        if (count($occurrences) !== 1) return new ErrorResponse(400,'Unable to match the schedule for conversion.');

        // Create Shift
        $start = Carbon::instance(current($occurrences))->setTimezone('UTC');
        $shift = Shift::create([
            'business_id' => $this->business()->id,
            'caregiver_id' => $schedule->caregiver_id,
            'client_id' => $schedule->client_id,
            'checked_in_time' => $start,
            'checked_out_time' => $start->copy()->addMinutes($schedule->duration),
            'schedule_id' => $schedule->id,
            'hours_type' => $schedule->hours_type,
            'caregiver_rate' => $schedule->getCaregiverRate(),
            'provider_fee' => $schedule->getProviderFee(),
            'status' => Shift::WAITING_FOR_AUTHORIZATION,
        ]);
        return new CreatedResponse('The scheduled shift has been converted to an actual shift.', $shift->toArray());
    }
}
