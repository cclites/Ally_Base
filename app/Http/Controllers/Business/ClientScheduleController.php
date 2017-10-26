<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidStartDate;
use App\Rules\ValidTimezoneOrOffset;
use App\Schedule;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;
use App\Scheduling\ScheduleCreator;
use DB;
use Illuminate\Http\Request;

class ClientScheduleController extends BaseController
{
    /**
     * Retrieve aggregated list of events generated from all client schedules
     *
     * @param \Illuminate\Http\Request $request
     * @param $client_id
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function index(Request $request, $client_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $start = $request->input('start', date('Y-m-d', strtotime('First day of last month -2 months')));
        $end = $request->input('end', date('Y-m-d', strtotime('First day of this month +13 months')));

        if (strlen($start) > 10) $start = substr($start, 0, 10);
        if (strlen($end) > 10) $end = substr($end, 0, 10);

        $events = new ScheduleEventsResponse($client->getEvents($start, $end), 'business.clients.schedule.show', ['id' => $client->id]);
        return $events;
    }

    /**
     * Retrieve the details of a schedule
     *
     * @param $client_id
     * @param $schedule_id
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function show($client_id, $schedule_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $schedule = Schedule::findOrFail($schedule_id);
        return new ScheduleResponse($schedule);
    }

    /**
     * Create a new schedule or single event
     *
     * @param \Illuminate\Http\Request $request
     * @param $client_id
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function create(Request $request, $client_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'time' => 'required|date_format:H:i:s',
            'duration' => 'required|integer',
            'interval_type' => 'required|in:weekly,biweekly,monthly,bimonthly',
            'bydays' => ['required_if:interval_type,weekly,biweekly', new ValidStartDate($request->input('start_date'))],
            'caregiver_id' => 'nullable|integer',
            'caregiver_rate' => 'nullable|numeric',
            'provider_fee' => 'nullable|numeric',
            'notes' => 'nullable',
        ], [
            'bydays.required_if' => 'At least one day of the week is required.',
        ]);

        list($data['start_date'], $data['end_date']) = filter_dates($data['start_date'], $data['end_date']);
        if (!$data['end_date']) {
            $data['end_date'] = Schedule::FOREVER_ENDDATE;
        }

        $creator = new ScheduleCreator($data);
        try {
            $schedule = $creator->make(['business_id' => $this->business()->id]);
            if ($client->schedules()->save($schedule)) {
                return new CreatedResponse('The new schedule has been successfully created.');
            }
        }
        catch (InvalidScheduleParameters $e) {
            return new ErrorResponse(400, 'Invalid Parameters: ' . $e->getMessage());
        }

        return new ErrorResponse(500, 'The new schedule could not be created');
    }

    /**
     * Update an entire schedule after a selected_date
     *
     * @param \Illuminate\Http\Request $request
     * @param $client_id
     * @param $schedule_id
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function update(Request $request, $client_id, $schedule_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $schedule = Schedule::findOrFail($schedule_id);

        $data = $request->validate([
            'selected_date' => 'required|date',
            'end_date' => 'nullable|date',
            'time' => 'required|date_format:H:i:s',
            'duration' => 'required|integer',
            'interval_type' => 'required|in:weekly,biweekly,monthly,bimonthly',
            'bydays' => 'required_if:interval_type,weekly,biweekly',
            'caregiver_id' => 'nullable|integer',
            'caregiver_rate' => 'nullable|numeric',
            'provider_fee' => 'nullable|numeric',
            'notes' => 'nullable',
        ]);

        list($data['selected_date'], $data['end_date']) = filter_dates($data['selected_date'], $data['end_date']);
        if (!$data['end_date']) {
            $data['end_date'] = Schedule::FOREVER_ENDDATE;
        }

        $creator = new ScheduleCreator($data);
        if (!$creator->hasChangesFrom($schedule)) {
            return new SuccessResponse('But no changes were made.');
        }

        try {
            DB::beginTransaction();

            if (!$schedule->closeSchedule($data['selected_date'])) {
                throw new \Exception('Unable to close previous schedule');
            }

            if (!$newSchedule = $creator->recreate($schedule)) {
                throw new \Exception('Unable to create new schedule after closing old schedule.');
            }

            DB::commit();
            return new SuccessResponse('The selected date has been updated.', ['old_id' => $schedule->id, 'new_id' => $newSchedule->id]);
        }
        catch(\Exception $e) {
            throw $e;
        }

        DB::rollBack();
        return new ErrorResponse(500, 'Unable to update selected date: ' . $e->getMessage());
    }

    /**
     * Delete an entire schedule
     *
     * @param $client_id
     * @param $schedule_id
     *
     * @return \App\Http\Controllers\Business|\Illuminate\Contracts\Support\Responsable
     */
    public function destroy(Request $request, $client_id, $schedule_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $schedule = Schedule::findOrFail($schedule_id);

        $data = $request->validate([
            'selected_date' => 'required|date',
        ]);

        $data['selected_date'] = filter_date($data['selected_date']);

        if ($schedule->closeSchedule($data['selected_date'])) {
            return new SuccessResponse('The schedule has been deleted for ' . $data['selected_date'] . ' and later.');
        }

        return new ErrorResponse(500, 'Unable to delete schedule.');
    }

    /**
     * Create a single event
     *
     * @param \Illuminate\Http\Request $request
     * @param $client_id
     */
    public function createSingle(Request $request, $client_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $data = $request->validate([
            'start_date' => 'required|date',
            'time' => 'required|date_format:H:i:s',
            'duration' => 'required|integer',
            'caregiver_id' => 'nullable|integer',
            'caregiver_rate' => 'nullable|numeric',
            'provider_fee' => 'nullable|numeric',
            'notes' => 'nullable',
        ]);

        $data['start_date'] = filter_date($data['start_date']);

        $schedule = new Schedule([
            'business_id' => $this->business()->id,
            'notes' => $data['notes'] ?? null,
            'caregiver_id' => $data['caregiver_id'] ?? null,
            'caregiver_rate' => $data['caregiver_rate'] ?? null,
            'provider_fee' => $data['provider_fee'] ?? null,
        ]);
        $schedule->setSingleEvent($data['start_date'], $data['time'], $data['duration']);
        if ($client->schedules()->save($schedule)) {
            return new CreatedResponse('The single event has been created successfully.');
        }
        return new ErrorResponse(500, 'Unable to create event.');
    }

    /**
     * "Update" a single event
     *
     * @param \Illuminate\Http\Request $request
     * @param $client_id
     * @param $schedule_id
     * @param $date
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function updateSingle(Request $request, $client_id, $schedule_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $schedule = Schedule::findOrFail($schedule_id);

        $data = $request->validate([
            'selected_date' => 'required|date',
            'time' => 'required|date_format:H:i:s',
            'duration' => 'required|integer',
            'caregiver_id' => 'nullable|integer',
            'caregiver_rate' => 'nullable|numeric',
            'provider_fee' => 'nullable|numeric',
            'notes' => 'nullable',
        ]);

        $data['selected_date'] = filter_date($data['selected_date']);

        if ($schedule->isSingle()) {
            $schedule->setSingleEvent($data['selected_date'], $data['time'], $data['duration']);
            $schedule->fill([
                'caregiver_id' => $data['caregiver_id'] ?? null,
                'caregiver_rate' => $data['caregiver_rate'] ?? null,
                'provider_fee' => $data['provider_fee'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $schedule->save();
            return new SuccessResponse('The selected date has been updated.', ['old_id' => $schedule->id, 'new_id' => $schedule->id]);
        }

        // Recurring: Create a schedule exception then a new single event with the new data
        try {
            DB::beginTransaction();

            if (!$schedule->createException($data['selected_date'])) {
                throw new \Exception('Schedule exception creation failed.');
            }

            $newSchedule = $schedule->replicate(['id', 'rrule']);
            $newSchedule->setSingleEvent($data['selected_date'], $data['time'], $data['duration']);
            $newSchedule->fill([
                'caregiver_id' => $data['caregiver_id'] ?? null,
                'caregiver_rate' => $data['caregiver_rate'] ?? null,
                'provider_fee' => $data['provider_fee'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            if (!$newSchedule->save()) {
                throw new \Exception('Unable to create new single event after exception.');
            }

            DB::commit();
            return new SuccessResponse('The selected date has been updated.', ['old_id' => $schedule->id, 'new_id' => $newSchedule->id]);
        }
        catch(\Exception $e) {
            DB::rollBack();
            return new ErrorResponse(500, 'Unable to update selected date.');
        }
    }

    /**
     * Create a schedule exception
     *
     * @param $client_id
     * @param $schedule_id
     * @param $date
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function destroySingle(Request $request, $client_id, $schedule_id)
    {
        $client = Client::findOrFail($client_id);
        if (!$this->business()->clients()->where('id', $client->id)->exists()) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $schedule = Schedule::findOrFail($schedule_id);

        $data = $request->validate([
            'selected_date' => 'required|date',
        ]);

        $data['selected_date'] = filter_date($data['selected_date']);

        if ($schedule->createException($data['selected_date'])) {
            return new SuccessResponse('The selected date has been deleted.');
        }
        return new ErrorResponse(500, 'Could not delete the selected date.');
    }


}
