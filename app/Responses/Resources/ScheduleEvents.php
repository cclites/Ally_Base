<?php

namespace App\Responses\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ScheduleEvents implements Responsable
{
    protected $events;
    protected $routeName;
    protected $routeParams;
    protected $additionalOptions = [
            'all' => [],
        ];

    public function __construct($events, $routeName=null, $routeParams = [])
    {
        $this->events = $events;
        $this->routeName = $routeName;
        $this->routeParams = $routeParams;
    }

    public function additionalOptions($schedule_id, $options)
    {
        if (!$schedule_id) $schedule_id = 'all';
        $this->additionalOptions[$schedule_id] = $options;
        return $this;
    }

    public function toArray()
    {
        return array_map(function($array) {
            $this->routeParams['schedule_id'] = $array['schedule_id'];
            $additionalOptions = array_merge(
                $this->additionalOptions['all'],
                ($this->additionalOptions[$array['schedule_id']] ?? [])
            );
            return array_merge([
                'id' => $array['schedule_id'],
                'title' => $array['title'],
                'start' => $array['start']->format(\DateTime::ISO8601),
                // Needs to add 1 second to end time for FullCalendar support
                'end' => $array['end']->add(new \DateInterval('PT1S'))->format(\DateTime::ISO8601),
//                'url' => route($this->routeName, $this->routeParams)
            ], $additionalOptions);
        }, $this->events);
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
}
