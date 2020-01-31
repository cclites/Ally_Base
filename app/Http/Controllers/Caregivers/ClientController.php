<?php
namespace App\Http\Controllers\Caregivers;

use App\Businesses\Timezone;
use App\Client;
use App\Exceptions\UnverifiedLocationException;
use App\Scheduling\ScheduleAggregator;
use App\Shifts\ClockIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Schedule;
use App\Shift;

class ClientController extends BaseController
{
    protected $includedRelations = ['evvAddress', 'evvPhone', 'careDetails', 'medications'];

    /**
     * List all clients the caregiver is assigned to
     *
     * @return \App\Client[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index(Request $request)
    {
        $query = $this->caregiver()->clients()
            ->with($this->includedRelations)
            ->orderByName();

        if($request->active){
            $query->where('active', $request->active);
        }

        $clients = $query->get();

        if ($request->expectsJson()) {
            return $clients;
        }

        return view('caregivers.client_list', compact('clients'));
    }

    public function show(Client $client)
    {
        if (!$this->caregiver()->clients()->where('client_id', $client->id)->exists()) abort(403);

        $client->load($this->includedRelations);
        return $client;
    }

    /**
     * Verify if the caregiver is at the client's address
     *
     * @param \App\Client $client
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyLocation(Client $client, Request $request)
    {
        $clockIn = new ClockIn($this->caregiver());
        $clockIn->setGeocode($request->input('latitude'), $request->input('longitude'));
        $evvData = $clockIn->getClockInVerificationData($client)->toArray();
        $verified = $evvData['checked_in_verified'] ?? false;

        return response()->json(['success' => $verified]);
    }

    /**
     * Return the current available schedules for a caregiver and client
     *
     * @param \App\Client $client
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function currentSchedules(Client $client)
    {
        $now = Carbon::now();
        $start = $now->copy()->startOfDay();
        $end = $now->copy()->endOfDay();

        if ($start->isAfter($now->copy()->subHours(2))) {
            $start = $now->copy()->subHours(2);
        }

        if ($end->isBefore($now->copy()->addHours(2))) {
            $end = $now->copy()->addHours(2);
        }

        $schedules = Schedule::forClient($client->id)
            ->forCaregiver($this->caregiver()->id)
            ->betweenDates($start, $end)
            ->thatCanBeClockedIn()
            ->get();

        // Sort schedules by closest starts_at
        if ($schedules->count() > 1) {
            $schedules = $schedules->sort(function($a, $b) use ($now) {
                $diffA = $now->diffInSeconds($a->starts_at);
                $diffB = $now->diffInSeconds($b->starts_at);
                if ($diffA == $diffB) {
                    return 0;
                }
                return ($diffA < $diffB) ? -1 : 1;
            })->values();
        }

        $schedules->map(function(Schedule $schedule) {
            $schedule->start_date = $schedule->startsAtString();
        });

        return $schedules;
    }

    /**
     * Get Caregiver information from the schedules surrounding
     * the current time or the current shift.
     *
     * @param \Illuminate\Http\Request $request
     * @param Client $client
     * @return \Illuminate\Http\Response
     */
    public function adjoiningSchedules(Request $request, Client $client)
    {
        $start = Carbon::now()->setTimezone($client->getTimezone());
        $end = Carbon::now()->setTimezone($client->getTimezone());

        if ($request->filled('shift')) {
            $shift = Shift::findOrFail($request->shift);

            if (! empty($shift->checked_in_time)) {
                $start = $shift->checked_in_time;
            }
            if (! empty($shift->schedule)) {
                $start = Carbon::parse($shift->schedule->starts_at);
                $end = $start->copy()->addMinutes($shift->schedule->duration);
            }
        }

        list($before, $after) = Schedule::getAdjoiningCaregiverSchedules($client, $start, $end, auth()->user()->id, 4);

        return response()->json(compact(['before', 'after']));
    }
}