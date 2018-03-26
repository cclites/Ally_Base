<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Http\Requests\CreateScheduleRequest;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidStartDate;
use App\Rules\ValidTimezoneOrOffset;
use App\Schedule;
use App\Responses\Resources\ScheduleEvents as ScheduleEventsResponse;
use App\Responses\Resources\Schedule as ScheduleResponse;
use App\Scheduling\ScheduleAggregator;
use App\Scheduling\ScheduleCreator;
use App\Traits\Request\ClientScheduleRequest;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ClientScheduleController extends BaseController
{
    /**
     * Retrieve aggregated list of events generated from all client schedules
     *
     * @param \Illuminate\Http\Request $request
     * @param $client
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function index(Request $request, ScheduleAggregator $aggregator, Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $aggregator->where('client_id', $client->id);

        $start = new Carbon(
            $request->input('start', date('Y-m-d', strtotime('First day of this month'))),
            $this->business()->timezone
        );
        $end = new Carbon(
            $request->input('end', date('Y-m-d', strtotime('First day of next month'))),
            $this->business()->timezone
        );

        $events = new ScheduleEventsResponse($aggregator->getSchedulesBetween($start, $end));
        return $events;
    }

    /**
     * Retrieve the details of a schedule
     *
     * @param $client
     * @param $schedule
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function show(Client $client, Schedule $schedule)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }
        
        return new ScheduleResponse($schedule);
    }

    /**
     * Create a new schedule or single event
     *
     * @param \App\Http\Requests\CreateScheduleRequest $request
     * @param $client
     *
     * @return \Illuminate\Contracts\Support\Responsable
     * @throws \Exception
     */
    public function create(CreateScheduleRequest $request, Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $data = $this->validateScheduleStore($request);
        if (!$data['end_date']) {
            $data['end_date'] = Schedule::FOREVER_ENDDATE;
        }

        $creator = new ScheduleCreator($data);
        try {
            DB::beginTransaction();
            $schedule = $creator->make(['business_id' => $this->business()->id]);
            if ($client->schedules()->save($schedule)) {
                if ($this->weeklyHoursGreaterThanMax($schedule) && !$request->input('override_max_hours')) {
                    DB::rollBack();
                    return new ErrorResponse(449, 'This update will result in the client\'s maximum weekly hours being exceeded');
                }
                DB::commit();
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
     * @param $client
     * @param $schedule
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function update(Request $request, Client $client, Schedule $schedule)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

        $data = $this->validateScheduleUpdate($request, $schedule);
        if (!$data['end_date']) {
            $data['end_date'] = Schedule::FOREVER_ENDDATE;
        }

        $durationChanged = ($data['duration'] != $schedule->duration);

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

            if ($durationChanged && $this->weeklyHoursGreaterThanMax($newSchedule) && !$request->input('override_max_hours')) {
                DB::rollBack();
                return new ErrorResponse(449, 'This update will result in the client\'s maximum weekly hours being exceeded');
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
     * @param $client
     * @param $schedule
     *
     * @return \App\Http\Controllers\Business|\Illuminate\Contracts\Support\Responsable
     */
    public function destroy(Request $request, Client $client, Schedule $schedule)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

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
     * @param $client
     */
    public function createSingle(Request $request, Client $client)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        $data = $this->validateScheduleStoreSingle($request);
        $data['business_id'] = $this->business()->id;

        DB::beginTransaction();
        $schedule = new Schedule($data);
        $schedule->setSingleEvent($data['start_date'], $data['time'], $data['duration']);
        if ($client->schedules()->save($schedule)) {
            if ($this->weeklyHoursGreaterThanMax($schedule) && !$request->input('override_max_hours')) {
                DB::rollBack();
                return new ErrorResponse(449, 'This update will result in the client\'s maximum weekly hours being exceeded');
            }
            DB::commit();
            return new CreatedResponse('The single event has been created successfully.');
        }
        return new ErrorResponse(500, 'Unable to create event.');
    }

    /**
     * "Update" a single event
     *
     * @param \Illuminate\Http\Request $request
     * @param $client
     * @param $schedule
     * @param $date
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function updateSingle(Request $request, Client $client, Schedule $schedule)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

        $data = $this->validateScheduleUpdateSingle($request, $schedule);
        // Correct $data for use in model
        $selected_date = filter_date($data['selected_date']);
        unset($data['selected_date']);
        $data['business_id'] = $this->business()->id;

        $durationChanged = ($data['duration'] != $schedule->duration);

        DB::beginTransaction();

        if ($schedule->isSingle()) {
            $schedule->fill($data);
            $schedule->setSingleEvent($selected_date, $data['time'], $data['duration']);
            $schedule->save();

            if ($durationChanged && $this->weeklyHoursGreaterThanMax($schedule) && !$request->input('override_max_hours')) {
                DB::rollBack();
                return new ErrorResponse(449, 'This update will result in the client\'s maximum weekly hours being exceeded');
            }

            DB::commit();
            return new SuccessResponse('The selected date has been updated.', ['old_id' => $schedule->id, 'new_id' => $schedule->id]);
        }

        // Recurring: Create a schedule exception then a new single event with the new data
        try {
            if (!$schedule->createException($selected_date)) {
                throw new \Exception('Schedule exception creation failed.');
            }

            $newSchedule = $schedule->replicate(['id', 'rrule']);
            $newSchedule->fill($data);
            $newSchedule->setSingleEvent($selected_date, $data['time'], $data['duration']);
            if (!$newSchedule->save()) {
                throw new \Exception('Unable to create new single event after exception.');
            }

            if ($this->weeklyHoursGreaterThanMax($newSchedule) && !$request->input('override_max_hours')) {
                DB::rollBack();
                return new ErrorResponse(449, 'This update will result in the client\'s maximum weekly hours being exceeded');
            }

            DB::commit();
            return new SuccessResponse('The selected date has been updated.', ['old_id' => $schedule->id, 'new_id' => $newSchedule->id]);
        }
        catch(\Exception $e) {
            DB::rollBack();
            return new ErrorResponse(500, 'Unable to update selected date.' . $e->getMessage());
        }
    }

    /**
     * Create a schedule exception
     *
     * @param $client
     * @param $schedule_id
     * @param $date
     *
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function destroySingle(Request $request, Client $client, Schedule $schedule)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if (!$this->businessHasSchedule($schedule)) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

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
