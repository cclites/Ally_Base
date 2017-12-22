<?php

namespace App\Responses\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ScheduleEvents implements Responsable
{
    public $events;
    protected $routeName;
    protected $routeParams;
    protected $additionalOptions = [
            'all' => [],
        ];

    protected $backgroundColors = [
        'past' => [
            '#7f7f6f',
            '#59665b',
            '#849290',
            '#adad85',
            '#999966',
            '#85858e',
        ],
        'current' => [
            '#34A82D',
            '#27c11e',
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
            if (!empty($array['checked_in'])) {
                $backgroundColor = $this->getBackgroundColor('current', $array['title']);
                $array['title'] .= ': Clocked In';
            }
            elseif($array['end']->format('U') < time()) {
                $backgroundColor = $this->getBackgroundColor('past', $array['title']);
            }
            else {
                $backgroundColor = $this->getBackgroundColor('future', $array['title']);
            }
            return array_merge([
                'id' => $array['schedule_id'],
                'title' => $array['title'],
                'start' => $array['start']->format(\DateTime::ISO8601),
                // Needs to add 1 second to end time for FullCalendar support
                'end' => $array['end']->add(new \DateInterval('PT1S'))->format(\DateTime::ISO8601),
                'backgroundColor' => $backgroundColor,
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
}
