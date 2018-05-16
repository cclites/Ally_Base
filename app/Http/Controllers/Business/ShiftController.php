<?php

namespace App\Http\Controllers\Business;

use App\Events\UnverifiedShiftConfirmed;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Shift;
use App\ShiftIssue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ShiftController extends BaseController
{
    public function index()
    {
        // TODO: redirect to Shift Report
    }

    public function create()
    {
        $activities = $this->business()->allActivities();
        return view('business.shifts.create', compact('activities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'client_id' => 'required|exists:clients,id',
                'caregiver_id' => 'required|exists:caregivers,id',
                'caregiver_comments' => 'nullable',
                'mileage' => 'nullable|numeric|max:1000|min:0',
                'other_expenses' => 'nullable|numeric|max:1000|min:0',
                'other_expenses_desc' => 'nullable',
                'checked_in_time' => 'required|date',
                'checked_out_time' => 'required|date|after_or_equal:' . $request->input('checked_in_time'),
                'caregiver_rate' => 'required|numeric|max:1000|min:0',
                'provider_fee' => 'required|numeric|max:1000|min:0',
                'hours_type' => 'required|in:default,overtime,holiday',
            ],
            [
                'checked_out_time.after_or_equal' => 'The clock out time cannot be less than the clock in time.'
            ]
        );

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);
        $data['business_id'] = $this->business()->id;
        $data['status'] = Shift::WAITING_FOR_AUTHORIZATION;
        $data['mileage'] = request('mileage', 0);
        $data['other_expenses'] = request('other_expenses', 0);
        $data['verified'] = false;

        if ($shift = Shift::create($data)) {
            $shift->activities()->sync($request->input('activities', []));
            foreach ($request->input('issues', []) as $issue) {
                $issue = new ShiftIssue([
                    'caregiver_injury' => $issue['caregiver_injury'] ?? 0,
                    'client_injury' => $issue['client_injury'] ?? 0,
                    'comments' => $issue['comments'] ?? ''
                ]);
                $shift->issues()->save($issue);
            }
            $redirect = $request->input('modal') == 1 ? null : route('business.shifts.show', [$shift->id]);
            return new SuccessResponse('You have successfully created this shift.', ['shift' => $shift->id], $redirect);
        }

        return new ErrorResponse(500, 'Error creating the shift.');
    }

    public function show(Request $request, Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Load needed relationships
        $shift->load(['activities', 'issues', 'schedule', 'client', 'signature', 'statusHistory']);
        $shift->append(['ally_pct']);

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
        
        // // Map additional data
        $shift->append(['charged_at', 'confirmed_at']);

        $activities = $shift->business->allActivities();
        return view('business.shifts.show', compact('shift', 'checked_in_distance', 'checked_out_distance', 'activities'));
    }

    public function update(Request $request, Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Allow admin overrides on locked shifts
        if (is_admin() && $request->input('override')) {
            $adminOverride = true;
        } else if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }

        $data = $request->validate(
            [
                'client_id' => 'required|exists:clients,id',
                'caregiver_id' => 'required|exists:caregivers,id',
                'caregiver_comments' => 'nullable',
                'mileage' => 'nullable|numeric|max:1000|min:0',
                'other_expenses' => 'nullable|numeric|max:1000|min:0',
                'other_expenses_desc' => 'nullable',
                'checked_in_time' => 'required|date',
                'checked_out_time' => 'required|date|after_or_equal:' . $request->input('checked_in_time'),
                'caregiver_rate' => 'required|numeric|max:1000|min:0',
                'provider_fee' => 'required|numeric|max:1000|min:0',
                'hours_type' => 'required|in:default,overtime,holiday',
            ],
            [
                'checked_out_time.after_or_equal' => 'The clock out time cannot be less than the clock in time.'
            ]
        );

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);
        $data['mileage'] = request('mileage', 0);
        $data['other_expenses'] = request('other_expenses', 0);

        if ($shift->update($data)) {
            if (isset($adminOverride)) {
                // Update persisted costs
                $shift->costs()->persist();
            }
            $shift->activities()->sync($request->input('activities', []));
            return new SuccessResponse('You have successfully updated this shift.');
        }
        return new ErrorResponse(500, 'The shift could not be updated.');
    }

    public function destroy(Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }
        if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }
        if ($shift->delete()) {
            return new SuccessResponse("This shift has been deleted.");
        }
        return new ErrorResponse(500, "This shift could not be deleted.");
    }

    public function confirm(Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if ($shift->statusManager()->ackConfirmation()) {
            if (!$shift->isVerified()) {
                event(new UnverifiedShiftConfirmed($shift));
            }
            return new SuccessResponse('The shift has been confirmed.', $shift->toArray());
        }

        if ($shift->statusManager()->isConfirmed()) {
            return new ErrorResponse(400, 'The shift has already been confirmed.');
        }
        return new ErrorResponse(500, 'The shift could not be confirmed due to a system error.');
    }

    public function unconfirm(Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if (!$shift->statusManager()->isConfirmed()) {
            return new ErrorResponse(400, 'The shift is already unconfirmed.');
        }

        if ($shift->statusManager()->unconfirm()) {
            return new SuccessResponse('The shift has been unconfirmed.', $shift->toArray());
        }

        return new ErrorResponse(400, 'The shift is locked for modification.');
    }

    public function printPage(Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Load needed relationships
        $shift->load('activities', 'issues', 'schedule', 'client', 'caregiver');

        // Calculate distances
        $checked_in_distance = null;
        $checked_out_distance = null;
        if ($address = $shift->client->evvAddress) {
            if ($shift->checked_in_latitude || $shift->checked_in_longitude) {
                $shift->checked_in_distance = $address->distanceTo($shift->checked_in_latitude, $shift->checked_in_longitude);
            }
            if ($shift->checked_out_latitude || $shift->checked_out_longitude) {
                $shift->checked_out_distance = $address->distanceTo($shift->checked_out_latitude, $shift->checked_out_longitude);
            }
        }

        $timezone = $this->business()->timezone;

        if (request()->filled('type') && strtolower(request('type')) == 'pdf') {
            // return pdf
            $pdf = PDF::loadView('business.shifts.print', compact('shift', 'timezone'));
            return $pdf->download('payment_details.pdf');
        }

        return view('business.shifts.print', compact('shift', 'timezone'));
    }

    public function storeIssue(Request $request, Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Load needed relationships
        $shift->load(['activities', 'issues']);

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
        if (!$this->businessHasShift($shift)) {
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
        if (!$schedule->caregiver_id) return new ErrorResponse(400, 'There is no caregiver assigned to this scheduled shift, cannot convert.');
        if (!$schedule->client_id) return new ErrorResponse(400, 'There is no client assigned to this scheduled shift, cannot convert.');

        $searchStart = (new Carbon($date, $this->business()->timezone))->setTime(0, 0, 0);
        $searchEnd = $searchStart->copy()->addDay();
        $occurrences = $schedule->getOccurrencesStartingBetween($searchStart, $searchEnd);

        // Make sure 1 occurrence was found
        if (count($occurrences) !== 1) return new ErrorResponse(400, 'Unable to match the schedule for conversion.');

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

    public function duplicate(Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Duplicate an existing shift and advance one day
        $shift = $shift->replicate();
        $shift->checked_in_time = (new Carbon($shift->checked_in_time))->addDay();
        $shift->checked_out_time = (new Carbon($shift->checked_out_time))->addDay();
        $shift->status = null;

        $checked_in_distance = null;
        $checked_out_distance = null;
        $activities = $shift->business->allActivities();

        return view('business.shifts.show', compact('shift', 'checked_in_distance', 'checked_out_distance', 'activities'));

    }

    /**
     * Handles manual clock out of shift for office users.
     *
     * @param Shift $shift
     * @return void
     */
    public function officeClockOut(Shift $shift)
    {
        if (!$this->businessHasShift($shift)) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        $data = request()->validate(
            [
                'checked_in_time' => 'required|date',
                'checked_out_time' => 'required|date|after_or_equal:' . request()->input('checked_in_time'),
            ],
            [
                'checked_out_time.after_or_equal' => 'The clock out time cannot be less than the clock in time.'
            ]
        );

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_method'] = Shift::METHOD_OFFICE;

        if ($shift->update($data)) {
            return new SuccessResponse('Shift was successfully clocked out.');
        }

        return new ErrorResponse(500, 'The shift could not be clocked out.');
    }
}
