<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientRate;
use App\Billing\ScheduleService;
use App\Business;
use App\Caregiver;
use App\CaregiverLicense;
use App\CaregiverScheduleRequest;
use App\Exceptions\AutomaticCaregiverAssignmentException;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\MaximumWeeklyHoursExceeded;
use App\Http\Requests\BulkDestroyScheduleRequest;
use App\Http\Requests\BulkUpdateScheduleRequest;
use App\Http\Requests\BusinessRequest;
use App\Http\Requests\CreateScheduleRequest;
use App\Http\Requests\PrintableScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Notifications\Caregiver\CertificationExpiring;
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
use App\Scheduling\ScheduleEditor;
use App\Scheduling\ScheduleWarningAggregator;
use App\Shift;
use App\Shifts\RateFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Client;
use Illuminate\Support\Facades\DB;

class ScheduleController extends BaseController
{
    public function index(Request $request)
    {
        $chain = $this->businessChain();

        return view('business.schedule', ['business' => $this->business(), 'weekStart' => $chain->calendar_week_start]);
    }

    public function events(Request $request)
    {
        $query = Schedule::forRequestedBusinesses()
            ->with(['client', 'caregiver', 'shifts', 'services', 'service', 'carePlan', 'services.service' ])
            ->ordered();

        // Filter by client or caregiver
        if ($client_id = $request->input('client_id')) {
            $query->where('client_id', $client_id);
        }

        if ($caregiver_id = $request->input('caregiver_id')) {
            $query->where('caregiver_id', $caregiver_id);
        } elseif ($request->input('caregiver_id') === '0') {
            $query->where('caregiver_id', null);
        }

        $start = Carbon::parse($request->input('start', 'First day of this month'));
        $end = Carbon::parse($request->input('end', 'First day of next month'));
        $schedules = $query->whereBetween('starts_at', [$start, $end])->get();

        $events = new ScheduleEventsResponse( $schedules );
        $events->setTitleCallback(function (Schedule $schedule) { return $this->businessScheduleTitle($schedule); });

        return [
            'kpis' => $events->kpis(),
            'events' => $events->toArray(),
        ];
    }

    /**
     * Display a listing of open shifts
     *
     * @return \Illuminate\Http\Response
     */
    public function openShifts()
    {
        if( !is_office_user() ) abort( 403 );

        $chain = $this->businessChain();

        if( request()->filled( 'json' ) ){

            $results = Schedule::forRequestedBusinesses()
                ->with([ 'client', 'schedule_requests' => function( $q ){

                    return $q->where( 'status', 'pending' );
                }])
                ->ordered()
                ->inTheNextMonth( $chain->businesses->first()->timezone )
                ->whereOpen()
                ->get();


            $schedules = $results->map( function( Schedule $schedule ) {

                return [

                    'id'                => $schedule->id,
                    'start'             => $schedule->starts_at->copy()->format( \DateTime::ISO8601 ),
                    'client'            => $schedule->client->nameLastFirst(),
                    'client_id'         => $schedule->client->id,
                    'start_time'        => $schedule->starts_at->copy()->format('g:i A'),
                    'end_time'          => $schedule->starts_at->copy()->addMinutes($schedule->duration)->addSecond()->format('g:i A'),
                    'requests_count'    => $schedule->schedule_requests->count()
                ];
            });

            return [ 'events' => $schedules, 'requests' => [] ];
        }

        return view( 'open_shifts', [ 'businesses' => $chain->id, 'role_type' => auth()->user()->role_type ]);
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
        $this->authorize('read', $schedule);
        return new ScheduleResponse($schedule->load('client', 'services'));
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
        $data = $request->filtered();
        $this->authorize('create', [Schedule::class, $data]);
        $business = $request->getBusiness();

        if (!$this->validateAgainstNegativeRates($request)) {
            return new ErrorResponse(400, 'The provider fee cannot be a negative number.');
        }

        $services = $request->getServices();

        \DB::beginTransaction();

        // Check if Caregiver is assigned to the client
        try {
            if ($this->ensureCaregiverAssignmentAndCreateDefaultRates($request)) {
                // Clear out the rates for all services so they are
                // pulled from the defaults that were just created.
                $request->caregiver_rate = null;
                $request->client_rate = null;

                $services = collect($services)->map(function ($service) {
                    $service['caregiver_rate'] = null;
                    $service['client_rate'] = null;
                    return $service;
                })->toArray();
            }
        } catch (AutomaticCaregiverAssignmentException $ex) {
            \DB::rollBack();
            return new ErrorResponse($ex->getStatusCode(), $ex->getMessage());
        }

        $creator->startsAt(Carbon::parse($request->input('starts_at')))
            ->duration($request->duration)
            ->assignments($business->id, $request->client_id, $request->caregiver_id, $request->service_id, $request->payer_id)
            ->rates($request->caregiver_rate, $request->client_rate, $request->fixed_rates)
            ->addServices($services);

        if ($request->hours_type == 'overtime') {
            $creator->overtime($request->overtime_duration);
        } elseif ($request->hours_type == 'holiday') {
            $creator->holiday($request->overtime_duration);
        }

        if ($request->care_plan_id) {
            $creator->attachCarePlan($request->care_plan_id);
        }

        if ($request->getNotes()) {
            $note = ScheduleNote::create(['note' => $request->getNotes()]);
            $creator->attachNote($note);
        }

        if ($request->interval_type) {
            $endDate = Carbon::parse($request->recurring_end_date);
            $creator->interval($request->interval_type, $endDate, $request->bydays ?? []);
        }

        if ($request->override_max_hours) {
            $creator->overrideMaxHours();
        }

        if ($request->quickbooks_service_id) {
            $creator->attachQuickbooksService($request->quickbooks_service_id);
        }

        try {
            $created = $creator->create($this->userSettings()->enable_schedule_groups());
            if ($count = $created->count()) {
                \DB::commit();
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
     * Update a schedule, or an entire related schedule group
     *
     * @param \App\Http\Requests\UpdateScheduleRequest $request
     * @param \App\Schedule $schedule
     * @param \App\Scheduling\ScheduleAggregator $aggregator
     * @param \App\Scheduling\ScheduleEditor $editor
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule, ScheduleAggregator $aggregator, ScheduleEditor $editor)
    {
        $business = $request->getBusiness();
        $this->authorize('update', $schedule);
        $this->authorize('read', $business);

        if (!$this->validateAgainstNegativeRates($request)) {
            return new ErrorResponse(400, 'The provider fee cannot be a negative number.');
        }

        if ($request->input('group_update') && !$schedule->group) {
            return new ErrorResponse(400, 'A group update was attempted without a schedule group');
        }

        $totalHours = $aggregator->getTotalScheduledHoursForWeekOf($schedule->starts_at, $schedule->client_id);
        $newTotalHours = $totalHours - ($schedule->duration / 60) + ($request->duration / 60);
        $client = Client::find($schedule->client_id);
        if (!$request->override_max_hours && $newTotalHours > $client->max_weekly_hours) {
            $e = new MaximumWeeklyHoursExceeded('The week of ' . $schedule->starts_at->toDateString() . ' exceeds the maximum allowed hours for this client.');
            return new ErrorResponse($e->getStatusCode(), $e->getMessage());
        }

        \DB::beginTransaction();
        $services = $request->getServices();
        $updatedData = $request->getScheduleData();

        // Check if Caregiver is assigned to the client
        try {
            if ($this->ensureCaregiverAssignmentAndCreateDefaultRates($request)) {
                // Clear out the rates for all services so they are
                // pulled from the defaults that were just created.
                $updatedData['caregiver_rate'] = null;
                $updatedData['client_rate'] = null;

                $services = collect($services)->map(function ($service) {
                    $service['caregiver_rate'] = null;
                    $service['client_rate'] = null;
                    return $service;
                })->toArray();
            }
        } catch (AutomaticCaregiverAssignmentException $ex) {
            \DB::rollBack();
            return new ErrorResponse($ex->getStatusCode(), $ex->getMessage());
        }

        // Weekday mapping
        $dowMap = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $weekdayInt = (int) $schedule->weekday;
        $weekdayText = $dowMap[$weekdayInt];

        switch($request->input('group_update')) {
            case 'total_all':
                $editor->updateGroup($schedule->group, $schedule, $updatedData, $request->getNotes(), $services);
                \DB::commit();
                return new SuccessResponse('All schedule occurrences have been updated.');
            case 'total_weekday':
                $editor->updateGroup($schedule->group, $schedule, $updatedData, $request->getNotes(), $services, $weekdayInt);
                \DB::commit();
                return new SuccessResponse("All $weekdayText occurrences have been updated.");
            case 'future_all':
                $editor->updateFuture($schedule->group, $schedule, $updatedData, $request->getNotes(), $services);
                \DB::commit();
                return new SuccessResponse('All future occurrences have been updated.');
            case 'future_weekday':
                $editor->updateFuture($schedule->group, $schedule, $updatedData, $request->getNotes(), $services, $weekdayInt);
                \DB::commit();
                return new SuccessResponse("All future $weekdayText occurrences have been updated.");
            default:
                $editor->updateSingle($schedule, $updatedData, $request->getNotes(), $services);
        }

        \DB::commit();

        return new SuccessResponse('The schedule has been updated.');
    }


    protected function validateAgainstNegativeRates(CreateScheduleRequest $request)
    {
        $client = $request->getClient();
        $services = $request->getServices();

        if (count($services)) {
            foreach($services as $service) {
                if ($service['client_rate'] === null) continue;
                if (app(RateFactory::class)->hasNegativeProviderFee($client, $service['client_rate'], $service['caregiver_rate'])) {
                    return false;
                }
            }
        } else if ($request->client_rate !== null) {
            if (app(RateFactory::class)->hasNegativeProviderFee($client, $request->client_rate, $request->caregiver_rate)) {
                return false;
            }
        }

        return true;
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
        $this->authorize('update', $schedule);

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
        $schedule->update(['status' => request()->status]);

        // clear caregiver if open shift
        if (in_array(request()->status, [Schedule::CAREGIVER_CANCELED, Schedule::OPEN_SHIFT])) {
            $schedule->update(['caregiver_id' => null]);
        }

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
        $this->authorize('delete', $schedule);

        // Schedules are soft deleted now so we do not have to worry about related shifts
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
        $query = $request->scheduleQuery();
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
        $query = $request->scheduleQuery();
        // Schedules are soft deleted now so we do not have to worry about related shifts
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
     * @param \App\Http\Requests\PrintableScheduleRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function print(PrintableScheduleRequest $request)
    {
        $start = Carbon::parse($request->input('start_date', 'First day of this month'));
        $end = Carbon::parse($request->input('end_date', 'First day of next month'));
        $group_by = $request->input('group_by');
        $business = $request->getBusiness();
        $schedules = $business->schedules()
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get();

        $schedules = $schedules->map(function ($schedule) {
            $schedule->date = $schedule->starts_at->format('m/d/Y');
            $schedule->ends_at = $schedule->starts_at->copy()->addMinutes($schedule->duration);
            return $schedule;
        });

        return view('business.schedule_print', compact('schedules', 'group_by'));
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
        $this->authorize('read', $schedule);
        $data = $schedule->load(['caregiver', 'client', 'service', 'services'])->toArray();

        if ($schedule->caregiver) {
            $phone = $schedule->caregiver->phoneNumbers()->where('type', 'primary')->first();
            if (!$phone) {
                $phone = $schedule->caregiver->phoneNumbers()->first();
            }

            if ($phone) {
                $data['caregiver_phone'] = $phone->number(true);
                $data['caregiver_phone_type'] = $phone->type;
            }

            $data['caregiver_address'] = $schedule->caregiver->address->fullAddress ?? null;
        }

        $data['start_date'] = $schedule->starts_at->toDateTimeString();
        $data['end_date'] = $schedule->starts_at->addMinutes($schedule->duration)->toDateTimeString();
        $data['client_address'] = $schedule->client->evvAddress->fullAddress ?? null;
        $data['client_phone'] = $schedule->client->evvPhone->number ?? null;

        if (count($schedule->services)) {
            $data['service_summary'] = $schedule->services->map(function ($item) {
                return ['name' => $item->service->name, 'duration' => $item->duration];
            })->values();
        } else {
            $duration = divide(floatval($schedule->duration), 60, 2);
            $data['service_summary'] = [
                [
                    'name' => $schedule->service->name,
                    'duration' => number_format($duration, 2),
                ]
            ];
        }
        return response()->json($data);
    }

    /**
     * Create a temp schedule object with the request data and
     * check if there are any warnings that should be displayed
     * to the OfficeUser.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function warnings(Request $request)
    {
        $schedule = Schedule::make([
            'caregiver_id' => $request->caregiver,
            'client_id' => $request->client,
            'starts_at' => Carbon::parse($request->starts_at, auth()->user()->officeUser->getTimezone()),
            'duration' => $request->duration,
            'payer_id' => $request->payer_id,
            'service_id' => $request->service_id,
        ]);
        $schedule->id = $request->id ? $request->id : null;

        $services = collect([]);
        foreach ($request->services as $service) {
            $services->push(ScheduleService::make($service));
        }
        $schedule->setRelation('services', $services);

        $aggregator = new ScheduleWarningAggregator($schedule);
        return response()->json($aggregator->getAll());
    }

    /**
     * Automatically assign Caregiver to the requested Client
     * and create ClientRate records for each service/payer.
     *
     * @param CreateScheduleRequest $request
     * @return bool
     * @throws AutomaticCaregiverAssignmentException
     */
    public function ensureCaregiverAssignmentAndCreateDefaultRates(CreateScheduleRequest $request) : bool
    {
        $client = Client::findOrFail($request->client_id);

        if ($request->caregiver_id && ! $client->hasCaregiver($request->caregiver_id)) {
            if (filled($request->service_id)) { // hourly or fixed rate
                if ($request->hours_type != Shift::HOURS_DEFAULT) {
                    throw new AutomaticCaregiverAssignmentException('Cannot create caregiver assignment because you are using HOL/OT rates.  If this is correct, you must assign the Caregiver manually from the Client\'s Caregivers & Rates tab.');
                }
                // Create default rates based on the rates in the request
                ClientRate::add($client, [
                    'caregiver_id' => $request->caregiver_id,
                    'effective_start' => date('Y') . '-01-01',
                    'effective_end' => '9999-12-31',
                    'caregiver_hourly_rate' => ($request->fixed_rates ? 0 : $request->caregiver_rate) ?? 0,
                    'client_hourly_rate' => ($request->fixed_rates ? 0 : $request->client_rate) ?? 0,
                    'caregiver_fixed_rate' => ($request->fixed_rates ? $request->caregiver_rate : 0) ?? 0,
                    'client_fixed_rate' => ($request->fixed_rates ? $request->client_rate : 0) ?? 0,
                    'service_id' => null, // Set default rates for ALL services
                    'payer_id' => $request->payer_id,
                ]);
            } else { // service breakout
                // Create default rates for each *UNIQUE* service entry
                $shouldBeServiceSpecific = $request->hasMultipleUniqueServices();

                foreach ($request->getServices() as $service) {
                    if (app(RateFactory::class)->matchingRateExists($client, Carbon::parse($request->starts_at)->toDateString(), $service['service_id'], $service['payer_id'], $request->caregiver_id)) {
                        throw new AutomaticCaregiverAssignmentException('Cannot create caregiver assignment because you have different rates for the same service/payer.  If this is correct, you must assign the Caregiver manually from the Client\'s Caregivers & Rates tab.');
                    }
                    if ($service['hours_type'] != Shift::HOURS_DEFAULT) {
                        throw new AutomaticCaregiverAssignmentException('Cannot create caregiver assignment because you are using HOL/OT rates.  If this is correct, you must assign the Caregiver manually from the Client\'s Caregivers & Rates tab.');
                    }
                    // Create default rates based on the rates in the request
                    ClientRate::add($client, [
                        'caregiver_id' => $request->caregiver_id,
                        'effective_start' => date('Y') . '-01-01',
                        'effective_end' => '9999-12-31',
                        'caregiver_hourly_rate' => $service['caregiver_rate'] ?? 0,
                        'client_hourly_rate' => $service['client_rate'] ?? 0,
                        'caregiver_fixed_rate' => 0,
                        'client_fixed_rate' => 0,
                        'service_id' => $shouldBeServiceSpecific ? $service['service_id'] : null, // Set rates to ALL unless multiple services exists
                        'payer_id' => $service['payer_id'],
                    ]);

                    $service['caregiver_rate'] = null;
                    $service['client_rate'] = null;
                }
            }
            return true;
        }
        return false;
    }
}
