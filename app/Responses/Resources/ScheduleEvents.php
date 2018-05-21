<?php

namespace App\Responses\Resources;

use App\Schedule;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

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

    /**
     * Background color themes for each schedule type.
     * 
     * Currently all backgrounds are hard coded in the toArray function.
     * To re-implement this feature, use the $this->getBackgroundColor method
     * to auto calculate one of the colors in these arrays.
     * 
     * @var array
     */
    protected $backgroundColors = [
        'past' => [
            '#849290',
            '#7f7f6f',
            '#59665b',
            '#adad85',
            '#999966',
            '#85858e',
        ],
        'current' => [
            '#27c11e',
            '#34A82D',
            '#32872d',
            '#009933',
            '#00cc44',
            '#008000',
            '#2d862d'
        ],
        'future' => [
            '#0000ff',
            '#0000b3',
            '#3333cc',
            '#24248f',
            '#1e88e5',
            '#095da6',
            '#7460ee',
            '#3d7a98',
            '#4fb7ea',
        ],
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
     * @return void
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

            if ($schedule->isClockedIn()) {
                $backgroundColor = '#27c11e'; // current 
                $title .= ': Clocked In';
            }
            else {
                switch($schedule->status) {
                    case Schedule::CLIENT_CANCELED:
                        $backgroundColor = '#d9c01c'; // client cancel
                        break; 
                    case Schedule::CAREGIVER_CANCELED:
                        $backgroundColor = '#d91c4e'; // CG cancel
                        break;
                    default:
                        $backgroundColor = '#1c81d9'; // ok / future
                        if($schedule->starts_at < Carbon::now()) {
                            $backgroundColor = '#849290'; // past
                        }
                        break;
                }
            }


            return array_merge([
                'id' => $schedule->id,
                'title' => $title,
                'start' => $schedule->starts_at->format(\DateTime::ISO8601),
                // Needs to add 1 extra second to end time for FullCalendar support
                'end' => $schedule->starts_at->copy()->addMinutes($schedule->duration)->addSecond()->format(\DateTime::ISO8601),
                'backgroundColor' => $backgroundColor,
                'care_plan' => $schedule->carePlan,

                'client' => $schedule->client->name(),
                'caregiver' => $schedule->caregiver ? $schedule->caregiver->name() : 'None',
                'start_time' => $schedule->starts_at->format('g:i A'),
                'end_time' => $schedule->starts_at->copy()->addMinutes($schedule->duration)->addSecond()->format('g:i A'),
                'note' => empty($schedule->note) ? '' : $schedule->note->note,
                'unassigned' => empty($schedule->caregiver),
                'status' => $schedule->status,
            ], $additionalOptions);
        });
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
     * Select a color from the background color series array based on the title
     * Every event with the same title should be the same color
     *
     * @param $series
     * @param $title
     *
     * @return mixed
     */
    protected function getBackgroundColor($series, $title)
    {
        $bgs = $this->backgroundColors[$series];
        $title = preg_replace('/[^a-zA-Z0-9]/', '', $title);
        $title = substr($title, 0, 6) . substr($title, -6);
        $id = base_convert($title, 36, 10) % (count($bgs)-1);
        return $bgs[$id];
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
