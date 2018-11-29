<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Http\Requests\CreateScheduleRequest;
use App\Responses\ConfirmationResponse;
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
        $this->authorize('update', $client);

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
        $this->authorize('read', $client);
        $this->authorize('read', $schedule);

        return new ScheduleResponse($schedule);
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
        $this->authorize('update', $client);
        $this->authorize('delete', $schedule);

        $data = $request->validate([
            'selected_date' => 'required|date',
        ]);

        $data['selected_date'] = filter_date($data['selected_date']);

        if ($schedule->closeSchedule($data['selected_date'])) {
            return new SuccessResponse('The schedule has been deleted for ' . $data['selected_date'] . ' and later.');
        }

        return new ErrorResponse(500, 'Unable to delete schedule.');
    }

}
