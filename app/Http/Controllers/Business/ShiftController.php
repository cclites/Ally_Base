<?php

namespace App\Http\Controllers\Business;

use App\Events\UnverifiedShiftConfirmed;
use App\Http\Requests\UpdateShiftRequest;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\Shift;
use App\ShiftIssue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Arr;

class ShiftController extends BaseController
{
    public function index()
    {
        return redirect()->route('business.reports.shifts');
    }

    public function create()
    {
        $activities = $this->business()->allActivities();
        return view('business.shifts.create', compact('activities'));
    }

    /**
     * @param \App\Http\Requests\UpdateShiftRequest $request
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function store(UpdateShiftRequest $request)
    {
        $data = $request->filtered();
        $data['status'] = Shift::WAITING_FOR_AUTHORIZATION;
        $data['verified'] = false;

        $this->authorize('create', [Shift::class, $data]);

        if ($shift = Shift::create($data)) {
            $shift->activities()->sync($request->getActivities());
            $shift->syncIssues($request->getIssues());
            $redirect = $request->input('modal') == 1 ? null : route('business.shifts.show', [$shift->id]);
            return new SuccessResponse('You have successfully created this shift.', ['shift' => $shift->id], $redirect);
        }

        return new ErrorResponse(500, 'Error creating the shift.');
    }

    public function show(Request $request, Shift $shift)
    {
        $this->authorize('read', $shift);

        // Load needed relationships
        $shift->load(['activities', 'issues', 'schedule', 'client', 'caregiver', 'signature', 'statusHistory', 'goals', 'questions']);
        $shift->append(['ally_pct', 'charged_at', 'confirmed_at']);

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
                'address' => optional($shift->address)->only(['latitude', 'longitude']),
            ];

            return response()->json($data);
        }

        $activities = $shift->business->allActivities();

        return view('business.shifts.show', compact('shift', 'checked_in_distance', 'checked_out_distance', 'activities'));
    }

    /**
     * @param \App\Http\Requests\UpdateShiftRequest $request
     * @param \App\Shift $shift
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     */
    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        $this->authorize('update', $shift);

        // Allow admin overrides on locked shifts
        if (is_admin() && $request->input('override')) {
            $adminOverride = true;
        } else if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }

        $data = $request->filtered();

        $allQuestions = $shift->business->questions()->forType($shift->client->client_type)->get();
        if ($allQuestions->count() > 0) {
            $fields = [];
            foreach($allQuestions as $q) {
                if ($q->required == 1) {
                    $fields['questions.' . $q->id] = 'required';
                }
            }

            $questionData = $this->validate($fields, ['questions.*' => 'Please answer all required questions.']);
        }

        if ($shift->update($data)) {
            if (isset($adminOverride)) {
                // Update persisted costs
                $shift->costs()->persist();
            }

            $shift->activities()->sync($request->getActivities());
            $shift->syncIssues($request->getIssues());
            $shift->syncGoals($request->goals);
            $shift->syncQuestions($allQuestions, $questionData['questions'] ?? []);

            return new SuccessResponse('You have successfully updated this shift.');
        }
        return new ErrorResponse(500, 'The shift could not be updated.');
    }

    public function destroy(Shift $shift)
    {
        $this->authorize('delete', $shift);

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
        $this->authorize('update', $shift);

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
        $this->authorize('update', $shift);

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
        $this->authorize('read', $shift);

        // Load needed relationships
        $shift->load('activities', 'issues', 'schedule', 'client', 'caregiver');

        $timezone = $this->business()->timezone;

        if (request()->filled('type') && strtolower(request('type')) == 'pdf') {
            // return pdf
            $pdf = PDF::loadView('business.shifts.print', compact('shift', 'timezone'));
            return $pdf->download('payment_details.pdf');
        }

        return view('business.shifts.print', compact('shift', 'timezone'));
    }

    public function convertSchedule(Request $request, Schedule $schedule)
    {
        $this->authorize('read', $schedule);

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
        $this->authorize('read', $shift);

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
        $this->authorize('update', $shift);

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
