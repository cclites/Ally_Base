<?php

namespace App\Http\Controllers\Business;

use App\Events\UnverifiedShiftApproved;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Shift;
use App\ShiftIssue;
use Illuminate\Http\Request;

class ShiftController extends BaseController
{
    public function show(Request $request, $shift_id)
    {
        $shift = Shift::with(['activities', 'issues', 'schedule'])->findOrFail($shift_id);
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

    public function update(Request $request, $shift_id) {
        $shift = Shift::findOrFail($shift_id);
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
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
        ]);

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);

        if ($shift->update($data)) {
            $shift->activities()->sync($request->input('activities', []));
            return new SuccessResponse('You have successfully updated this shift.');
        }
        return new ErrorResponse(500, 'The shift could not be updated.');
    }

    public function verify($shift_id)
    {
        $shift = Shift::with(['activities', 'issues'])->findOrFail($shift_id);
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if ($shift->update(['verified' => true])) {
            event(new UnverifiedShiftApproved($shift));
            return new SuccessResponse('The shift has been verified');
        }

        return new ErrorResponse('The shift could not be verified');
    }

    public function storeIssue(Request $request, $shift_id)
    {
        $shift = Shift::with(['activities', 'issues'])->findOrFail($shift_id);
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

    public function updateIssue(Request $request, $shift_id, $issue_id)
    {
        $shift = Shift::with(['activities', 'issues'])->findOrFail($shift_id);
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
}
