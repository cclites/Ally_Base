<?php

namespace App\Scheduling;

class ScheduleAggregator
{
    protected $data = [];
    protected $activeSchedules = [];

    public function add($title, $schedule)
    {
        $this->data[] = [
            'title' => $title,
            'schedule' => $schedule
        ];
    }

    public function addActiveSchedules($active = [])
    {
        $active = (array) $active;
        $this->activeSchedules = array_merge($this->activeSchedules, $active);
    }

    public function events($start_date, $end_date, $timezone='UTC', $limitPerEvent = 300)
    {
        $events = [];
        foreach($this->data as $event) {
            $title       = $event['title'];
            $schedule    = $event['schedule'];
            $occurrences = $schedule->getOccurrencesBetween($start_date, $end_date, $timezone, $limitPerEvent);
            $events = array_merge($events, array_map(function ($date) use ($schedule, $title) {
                $end = clone $date;
                $end->add(new \DateInterval('PT' . $schedule->duration . 'M'));
                return [
                    'schedule_id' => $schedule->id,
                    'title'       => $title,
                    'start'       => $date,
                    'end'         => $end,
                    'duration'    => $schedule->duration,
                    'checked_in'=> in_array($schedule->id, $this->activeSchedules)
                ];
            }, $occurrences));
        }
        usort($events, function($eventA, $eventB) {
            $a = (int) $eventA['start']->format('U');
            $b = (int) $eventB['start']->format('U');
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });
        return $events;
    }
}
