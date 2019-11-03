<?php

namespace App\Responses\Resources;

use App\Schedule;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ScheduleEvents implements Responsable
{
    /**
     * @var \App\Schedule[]
     */
    public $schedules;

    /**
     * @var \Closure
     */
    protected $titleCallback;

    /**
     * @var array
     */
    protected $additionalOptions = [
            'all' => [],
        ];

    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    public function additionalOptions($schedule_id, $options)
    {
        if (!$schedule_id) $schedule_id = 'all';
        $this->additionalOptions[$schedule_id] = $options;
        return $this;
    }

    /**
     * Get stats on the schedules.
     *
     * @return array
     */
    public function kpis()
    {
        $totalShifts = 0;
        $unassignedShifts = 0;
        $totalMinutes = 0;
        $unassignedMinutes = 0;

        foreach($this->schedules as $s) {
            $totalMinutes += $s->duration;
            $totalShifts ++;

            if (empty($s->caregiver)) {
                $unassignedShifts++;
                $unassignedMinutes += $s->duration;
            }
        }

        return [
            'total_shifts' => $totalShifts,
            'total_hours' => floor($totalMinutes/60),
            'unassigned_shifts' => $unassignedShifts,
            'unassigned_hours' => floor($unassignedMinutes/60),
            'assigned_shifts' => $totalShifts - $unassignedShifts,
            'assigned_hours' => floor(($totalMinutes - $unassignedMinutes)/60),
        ];
    }

    public function toArray()
    {
        return $this->schedules->map(function(Schedule $schedule) {

            $additionalOptions = array_merge(
                $this->additionalOptions['all'],
                $this->additionalOptions[$schedule->id] ?? []
            );

            $title = $this->resolveEventTitle($schedule);

            if( is_caregiver() && $schedule->schedule_requests->count() ) $request_status = $schedule->schedule_requests->sortByDesc( 'pivot.created_at' )->first()->pivot->status;
            else $request_status = null;

            return array_merge([
                'id' => $schedule->id,
                'title' => $title,
                'start' => $schedule->starts_at->format(\DateTime::ISO8601),
                // Needs to add 1 extra second to end time for FullCalendar support
                'end' => $schedule->starts_at->copy()->addSeconds($schedule->duration * 60 + 1)->format(\DateTime::ISO8601),
                'duration' => $schedule->duration,
                'backgroundColor' => $this->getBackgroundColor($schedule),
                'care_plan' => $schedule->carePlan,
                'client' => $schedule->client->nameLastFirst(),
                'client_id' => $schedule->client->id,
                'caregiver' => $schedule->caregiver ? $schedule->caregiver->nameLastFirst() : 'OPEN',
                'caregiver_id' => $schedule->caregiver->id ?? 0,
                'start_time' => $schedule->starts_at->format('g:i A'),
                'end_time' => $schedule->starts_at->copy()->addMinutes($schedule->duration)->addSecond()->format('g:i A'),
                'note' => empty($schedule->note) ? '' : $schedule->note->note,
//                'unassigned' => empty($schedule->caregiver),
                'status' => $schedule->status,
                'shift_status' => $schedule->shift_status,
                'has_overtime' => $schedule->hasOvertime(),
                'added_to_past' => $schedule->added_to_past,
                'service_types' => $this->getServiceTypes($schedule),
                'requests_count' => $schedule->active_requests,
                'request_status' => $request_status
            ], $additionalOptions);
        });
    }

    public function getServiceTypes(Schedule $schedule) : Collection
    {
        if (count($schedule->services) > 0) {
            return $schedule->services->map(function ($item) {
                    return substr($item->service->name, 0, 3) . ':' . $item->duration;
                })->chunk(3)->map(function ($item) {
                    return $item->values();
                });
        } else if (filled($schedule->service)) {
            $duration = divide(floatval($schedule->duration), 60, 2);
            return collect([substr($schedule->service->name, 0, 3) . ':' . number_format($duration, 2)]);
        } else {
            return collect([]);
        }
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse($this->toArray());
    }

    /**
     * Determine the background color of the event, this should be synced with the css colors of BusinessSchedule
     *
     * @param \App\Schedule $schedule
     * @return string
     */
    protected function getBackgroundColor(Schedule $schedule)
    {
        $status = $schedule->status;
        $shift = $schedule->shift_status;

        if ($status === Schedule::ATTENTION_REQUIRED) {
            return '#C30000';
        }

        if ($status === Schedule::HOSPITAL_HOLD) {
            return '#9881e9';
        }

        if ($status === Schedule::CAREGIVER_CANCELED) {
            return '#ff8c00';
        }

        if ($status === Schedule::CLIENT_CANCELED) {
            return '#730073';
        }

        if ($status == Schedule::CAREGIVER_NOSHOW) {
            return '#63cbc7';
        }

        if ($schedule->hasOvertime()) {
            return '#fc4b6c';
        }

        if ($shift === Schedule::CLOCKED_IN) {
            return '#27c11e';
        }

        if ($shift === Schedule::MISSED_CLOCK_IN) {
            return '#E468B2';
        }

        if ($shift === Schedule::CONFIRMED) {
            return '#849290';
        }

        if ($shift === Schedule::UNCONFIRMED) {
            return '#ad92b0';
        }

        if (!$schedule->caregiver_id) {
            // Open shift
            return '#d9c01c';
        }

        if ($schedule->added_to_past) {
            return '#124aa5';
        }

        return '#1c81d9';
    }

    /**
     * Define a closure to use for formatting an event title
     *
     * @param \Closure $callback
     */
    public function setTitleCallback(\Closure $callback) {
        $this->titleCallback = $callback;
    }

    /**
     * Resolve event titles
     *
     * @param \App\Schedule $schedule
     * @return mixed
     */
    protected function resolveEventTitle(Schedule $schedule) {
        if ($this->titleCallback) return call_user_func($this->titleCallback, $schedule);

        return $schedule->client->name();
    }
}
