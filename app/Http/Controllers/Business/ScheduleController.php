<?php

namespace App\Http\Controllers\Business;

use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\MaximumWeeklyHoursExceeded;
use App\Http\Requests\BulkDestroyScheduleRequest;
use App\Http\Requests\BulkUpdateScheduleRequest;
use App\Http\Requests\CreateScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Responses\ConfirmationResponse;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;
use App\Responses\SuccessResponse;
use App\Schedule;
use App\ScheduleNote;
use App\Scheduling\ScheduleAggregator;
use App\Scheduling\ScheduleCreator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Client;

class ScheduleController extends BaseController
{
    public function index()
    {
        return view('business.schedule', ['business' => $this->business()]);
    }

    public function events(Request $request, ScheduleAggregator $aggregator)
    {
        $aggregator->where('business_id', $this->business()->id);

        // Filter by client or caregiver
        if ($client_id = $request->input('client_id')) {
            $aggregator->where('client_id', $client_id);
        }
        if ($caregiver_id = $request->input('caregiver_id')) {
            $aggregator->where('caregiver_id', $caregiver_id);
        } elseif ($request->input('caregiver_id') === '0') {
            $aggregator->where('caregiver_id', null);
        }

        $start = new Carbon(
            $request->input('start', date('Y-m-d', strtotime('First day of this month'))),
            $this->business()->timezone
        );
        $end = new Carbon(
            $request->input('end', date('Y-m-d', strtotime('First day of next month'))),
            $this->business()->timezone
        );

        $events = new ScheduleEventsResponse($aggregator->getSchedulesBetween($start, $end));
        $events->setTitleCallback(function (Schedule $schedule) { return $this->businessScheduleTitle($schedule); });

        return [
            'kpis' => $events->kpis(),
            'events' => $events->toArray(),
        ];
    }

    /**
     * Retrieve the details of a schedule
     *
     * @param \App\Schedule $schedule
     * @return \Illuminate\Contracts\Support\Responsable
     * @throws \Exception
     */
    public function show(Schedule $schedule)
    {
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.', $schedule);
        }

        return new ScheduleResponse($schedule->load('client'));
    }

    /**
     * Create a new schedule, including recurrence
     *
     * @param \App\Http\Requests\CreateScheduleRequest $request
     * @param \App\Scheduling\ScheduleCreator $creator
     * @return \App\Responses\CreatedResponse|\App\Responses\ErrorResponse
     * @throws \App\Exceptions\InvalidScheduleParameters
     * @throws \Exception
     */
    public function store(CreateScheduleRequest $request, ScheduleCreator $creator)
    {
        if (!$this->businessHasClient($request->client_id)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if ($request->caregiver_id && !$this->businessHasCaregiver($request->caregiver_id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $this->ensureCaregiverAssignment($request->client_id, $request->caregiver_id, $request->caregiver_rate, $request->provider_fee, $request->daily_rates);

        $startsAt = Carbon::createFromTimestamp($request->starts_at, $this->business()->timezone);
        $creator->startsAt($startsAt)
            ->duration($request->duration)
            ->assignments($this->business()->id, $request->client_id, $request->caregiver_id)
            ->rates($request->caregiver_rate, $request->provider_fee);

        if ($request->hours_type == 'overtime') {
            $creator->overtime($request->overtime_duration);
        } elseif ($request->hours_type == 'holiday') {
            $creator->holiday($request->overtime_duration);
        }

        if ($request->care_plan_id) {
            $creator->attachCarePlan($request->care_plan_id);
        }

        if ($request->notes) {
            $note = ScheduleNote::create(['note' => $request->notes]);
            $creator->attachNote($note);
        }

        if ($request->interval_type) {
            $endDate = Carbon::createFromTimestamp($request->recurring_end_date, $this->business()->timezone);
            $creator->interval($request->interval_type, $endDate, $request->bydays ?? []);
        }

        if ($request->override_max_hours) {
            $creator->overrideMaxHours();
        }

        try {
            $created = $creator->create();
            if ($count = $created->count()) {
                if ($count > 1) {
                    return new CreatedResponse('The scheduled shifts have been created.');
                }
                return new CreatedResponse('The scheduled shift has been created.');
            }
        } catch (MaximumWeeklyHoursExceeded $e) {
            return new ErrorResponse($e->getStatusCode(), $e->getMessage());
        } catch (InvalidScheduleParameters $e) {
            return new ErrorResponse(400, $e->getMessage());
        }

        return new ErrorResponse(500, 'Unknown error creating the schedules.');
    }

    /**
     * Update a single schedule
     *
     * @param \App\Http\Requests\UpdateScheduleRequest $request
     * @param \App\Schedule $schedule
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule, ScheduleAggregator $aggregator)
    {
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

        if ($schedule->starts_at < Carbon::now($this->business()->timezone)->setTime(0, 0)) {
            return new ErrorResponse(400, 'Past schedules are unable to be modified.');
        }

        if ($schedule->shifts->count()) {
            return new ErrorResponse(400, 'This schedule cannot be modified because it already has an active shift.');
        }

        $totalHours = $aggregator->getTotalScheduledHoursForWeekOf($schedule->starts_at, $schedule->client_id);
        $newTotalHours = $totalHours - ($schedule->duration / 60) + ($request->duration / 60);
        $client = Client::find($schedule->client_id);
        if (!$request->override_max_hours && $newTotalHours > $client->max_weekly_hours) {
            $e = new MaximumWeeklyHoursExceeded('The week of ' . $schedule->starts_at->toDateString() . ' exceeds the maximum allowed hours for this client.');
            return new ErrorResponse($e->getStatusCode(), $e->getMessage());
        }

        $notes = $request->input('notes');

        $this->ensureCaregiverAssignment($request->client_id, $request->caregiver_id, $request->caregiver_rate, $request->provider_fee, $request->daily_rates);

        if ($schedule->notes != $notes) {
            if (strlen($notes)) {
                $note = ScheduleNote::create(['note' => $notes]);
                $schedule->attachNote($note);
            } else {
                $schedule->deleteNote();
            }
        }

        $data = $request->validated();
        $data['starts_at'] = Carbon::createFromTimestamp($request->starts_at, $this->business()->timezone);
        unset($data['notes']);
        $schedule->update($data);
        return new SuccessResponse('The schedule has been updated.');
    }

    /**
     * Protected function for making sure a client caregiver relationship exists
     *
     * @param $client_id
     * @param $caregiver_id
     */
    protected function ensureCaregiverAssignment($client_id, $caregiver_id, $caregiver_rate, $provider_fee, $daily = false)
    {
        // attach caregiver to client if relationship doesn't exist
        $client = Client::findOrFail($client_id);
        if ($caregiver_id && !$client->hasCaregiver($caregiver_id)) {
            $data = [
                'caregiver_daily_rate' => $daily ? $caregiver_rate : 0,
                'provider_daily_fee' => $daily ? $provider_fee : 0,
                'caregiver_hourly_rate' => $daily ? 0 : $caregiver_rate,
                'provider_hourly_fee' => $daily ? 0 : $provider_fee,
            ];

            $data = array_map('floatval', $data);

            $client->caregivers()->syncWithoutDetaching([$caregiver_id => $data]);
        }
    }

    /**
     * Update a single schedule status and notes.
     *
     * @param \App\Schedule $schedule
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function updateStatus(Schedule $schedule)
    {
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

        if ($schedule->shifts->count()) {
            return new ErrorResponse(400, 'This schedule cannot be modified because it already has an active shift.');
        }

        // update notes
        if (request()->has('notes')) {
            $notes = request()->notes;
            if ($schedule->notes != $notes) {
                if (strlen($notes)) {
                    $note = ScheduleNote::create(['note' => $notes]);
                    $schedule->attachNote($note);
                } else {
                    $schedule->deleteNote();
                }
                // Refresh the note relationship
                $schedule->load('note');
            }
        }

        // set status
        $status = request()->status;
        if (!in_array($status, [
            Schedule::OK,
            Schedule::CAREGIVER_CANCELED,
            Schedule::CLIENT_CANCELED,
            Schedule::CONFIRMED,
        ])) {
            $status = Schedule::OK;
        }
        $schedule->update(['status' => $status]);

        $events = new ScheduleEventsResponse(collect([$schedule]));
        $events->setTitleCallback(function (Schedule $schedule) { return $this->businessScheduleTitle($schedule); });
        $data = $events->toArray()[0];

        return new SuccessResponse('The schedule has been updated.', $data);
    }

    /**
     * Delete a single schedule
     *
     * @param \App\Schedule $schedule
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function destroy(Schedule $schedule)
    {
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

        if ($schedule->starts_at < Carbon::now($this->business()->timezone)->setTime(0, 0)) {
            return new ErrorResponse(400, 'Past schedules are unable to be deleted.');
        }

        if ($schedule->shifts->count()) {
            return new ErrorResponse(400, 'This schedule cannot be deleted because it already has an active shift.');
        }

        $schedule->delete();
        return new SuccessResponse('The scheduled shift has been deleted.');
    }

    /**
     * Bulk Update Schedules
     *
     * @param \App\Http\Requests\BulkUpdateScheduleRequest $request
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function bulkUpdate(BulkUpdateScheduleRequest $request, ScheduleAggregator $aggregator)
    {
        $query = $request->scheduleQuery()->where('business_id', $this->business()->id);
        $schedules = $query->get();
        $client = Client::find($request->client_id);

        $this->validateCaregiverAssignment($client);

        if (!$schedules->count()) {
            return new ErrorResponse(400, 'No matching schedules could be found.');
        }

        $updatedNotes = [];
        $weeks = [];

        /**
         * @var Schedule $schedule
         */
        \DB::beginTransaction();

        try {
            foreach ($query->get() as $schedule) {
                // get week range for schedule
                $weekStart = $schedule->starts_at->copy()->startOfWeek();
                $weekEnd = $schedule->starts_at->copy()->endOfWeek();
                $range = $weekStart->format('Ymd') . '-' . $weekEnd->format('Ymd');
                $weeks[$range] = $weekStart;

                $schedule = $this->updateScheduleWithNewValues($schedule, $request->getUpdateData(), $updatedNotes);
                $schedule->save();
            }

            if ($client) {
                // enumerate week ranges
                foreach ($weeks as $range => $date) {
                    $total = $aggregator->getTotalScheduledHoursForWeekOf($date, $client->id);

                    if ($total > $client->max_weekly_hours) {
                        throw new MaximumWeeklyHoursExceeded('Schedule NOT updated because the changes would violate the Max Hours in the Client Record. Please see the Max Hours/Service Orders in the client record.');
                    }
                }
            }
        } catch (MaximumWeeklyHoursExceeded $e) {
            \DB::rollBack();
            return new ConfirmationResponse($e->getMessage());
        }

        \DB::commit();

        return new SuccessResponse('Matching schedules have been updated.');
    }

    /**
     * Handle bulk updating all schedule fields.
     *
     * @param [type] $schedule
     * @param [type] $newData
     * @param [type] $updatedNotes
     * @return void
     */
    public function updateScheduleWithNewValues($schedule, $newData, &$updatedNotes)
    {
        foreach ($newData as $field => $value) {
            switch ($field) {
                case 'new_start_time':
                    $parts = explode(':', $value);
                    $schedule->starts_at = $schedule->starts_at->setTime((int) $parts[0], (int) $parts[1]);
                    break;

                case 'new_note_method':
                    $text = $newData['new_note_text'];
                    if (!strlen($text)) {
                        break;
                    }
                    if (!$schedule->note_id) {
                        $schedule->note_id = 0;
                    }
                    if (!isset($updatedNotes[$schedule->note_id])) {
                        $notes = '';
                        if ($value == 'append') {
                            $notes .= $schedule->notes . "\n\n";
                        }
                        $notes .= $text;
                        $note = ScheduleNote::create(['note' => $notes]);
                        $updatedNotes[$schedule->note_id] = $note;
                    }
                    $schedule->attachNote($updatedNotes[$schedule->note_id]);
                    break;

                case 'new_note_text':
                    // handled above
                    break;

                case 'new_overtime_duration':
                    if ($value == -1) {
                        $schedule->overtime_duration = $schedule->duration;
                        break;
                    }
                    $schedule->overtime_duration = $value;
                    break;

                case 'new_care_plan_id':
                    $schedule->care_plan_id = $value ?? null;
                    break;

                default:
                    $field = substr($field, 4);
                    $schedule->$field = $value;
            }
        }

        return $schedule;
    }

    /**
     * Bulk Delete Schedules
     *
     * @param \App\Http\Requests\BulkDestroyScheduleRequest $request
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function bulkDestroy(BulkDestroyScheduleRequest $request)
    {
        $query = $request->scheduleQuery()->where('business_id', $this->business()->id);
        $schedules = $query->get();

        if (!$schedules->count()) {
            return new ErrorResponse(400, 'No matching schedules could be found.');
        }

        foreach ($query->get() as $schedule) {
            $schedule->delete();
        }

        return new SuccessResponse('Matching schedules have been deleted.');
    }

    /**
     * Handles printable schedule report submission
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function print(Request $request, ScheduleAggregator $aggregator)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);
        $start = new Carbon(
            $request->input('start_date'),
            $this->business()->timezone
        );
        $end = (new Carbon(
            $request->input('end_date'),
            $this->business()->timezone
        ))->setTime(23, 59, 59);

        $aggregator->where('business_id', $this->business()->id);
        $schedules = $aggregator->getSchedulesBetween($start, $end);
        $schedules->map(function ($schedule) {
            $schedule->date = $schedule->starts_at->format('m/d/Y');
            $schedule->ends_at = $schedule->starts_at->copy()->addMinutes($schedule->duration);
            return $schedule;
        });
        return view('business.schedule_print', compact('schedules'));
    }

    protected function businessScheduleTitle(Schedule $schedule)
    {
        $clientName = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
        $caregiverName = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
        return $clientName . ' (' . $caregiverName . ')';
    }

    /**
     * @param $client
     */
    protected function validateCaregiverAssignment($client)
    {
        if (!$client) {
            // Disable updates to caregiver assignments for all clients
            request()->validate(
                ['new_caregiver_id' => 'nullable|integer|max:0'],
                ['new_caregiver_id.*' => 'You cannot update a caregiver for all clients.']
            );
        }

        if ($client) {
            // Require the caregiver assignment to exist for the client
            if (!$client->hasCaregiver(request('new_caregiver_id'))) {
                request()->validate(
                    ['new_caregiver_id' => 'nullable|integer|max:0'],
                    ['new_caregiver_id.*' => 'The newly selected caregiver is not assigned to the selected client.']
                );
            }
        }
    }

    public function preview(Schedule $schedule)
    {
        $data = $schedule->load(['caregiver', 'client'])->toArray();

        if ($schedule->caregiver) {
            $data['caregiver_name'] = $schedule->caregiver->nameLastFirst();
            $data['caregiver_email'] = $schedule->caregiver->email;

            $phone = $schedule->caregiver->phoneNumbers()->where('type', 'primary')->first();
            if (!$phone) {
                $phone = $schedule->caregiver->phoneNumbers()->first();
            }

            if ($phone) {
                $data['caregiver_phone'] = $phone->number(true);
                $data['caregiver_phone_type'] = $phone->type;
            }
        }

        $data['start_date'] = $schedule->starts_at->toDateTimeString();
        $data['end_date'] = $schedule->starts_at->addMinutes($schedule->duration)->toDateTimeString();
        $data['client_address'] = optional($schedule->client->evvAddress)->fullAddress;

        return response()->json($data);
    }
}
