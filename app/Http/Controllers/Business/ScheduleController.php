<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\CreateScheduleRequest;
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
        } elseif ($request->input('caregiver_id') === "0") {
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
        $events->setTitleCallback(function(Schedule $schedule) {
            $clientName = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $caregiverName = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            return $clientName . ' (' . $caregiverName . ')';
        });
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
    public function show($schedule_id)
    {
        $schedule = Schedule::findOrFail($schedule_id);

        if ($schedule->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this schedule.');
        }

        return new ScheduleResponse($schedule);
    }


    public function store(CreateScheduleRequest $request, ScheduleCreator $creator)
    {
        if (!$this->businessHasClient($request->client_id)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        if ($request->caregiver_id && !$this->businessHasCaregiver($request->caregiver_id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $startsAt = Carbon::createFromTimestamp($request->starts_at, $this->business()->timezone);
        $creator->startsAt($startsAt)
            ->duration($request->duration)
            ->assignments($this->business()->id, $request->client_id, $request->caregiver_id)
            ->rates($request->caregiver_rate, $request->provider_fee);

        if ($request->hours_type == 'overtime') {
            $creator->overtime($request->overtime_duration);
        }
        else if ($request->hours_type == 'holiday') {
            $creator->holiday($request->overtime_duration);
        }

        if ($request->notes) {
            $note = new ScheduleNote(['note' => $request->notes]);
            $creator->attachNote($note);
        }

        if ($request->interval_type) {
            $endDate = Carbon::createFromTimestamp($request->recurring_end_date, $this->business()->timezone);
            $creator->interval($request->interval_type, $endDate, $request->bydays ?? []);
        }

        $created = $creator->create();
        if ($count = $created->count()) {
            if ($count > 1) {
                return new CreatedResponse('The scheduled shifts have been created.');
            }
            return new CreatedResponse('The scheduled shift has been created.');
        }

        return new ErrorResponse(500, 'Unknown error');
    }


}
