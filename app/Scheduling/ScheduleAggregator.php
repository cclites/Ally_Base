<?php

namespace App\Scheduling;

use Carbon\Carbon;

class ScheduleAggregator
{
    protected $data = [];
    protected $activeSchedules = [];
    protected $onlyStartTime = true;

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

    /**
     * Only include the schedule if the date range matches the start time, not accounting for the end time
     *
     * @param bool $bool
     * @return $this
     */
    public function onlyStartTime(bool $bool=true)
    {
        $this->onlyStartTime = $bool;
        return $this;
    }

    /**
     * @param string|\DateTime $start_date
     * @param string|\DateTime $end_date
     * @param string $timezone
     * @param int $limitPerEvent
     *
     * @return array
     */
    public function events($start_date, $end_date, $timezone='UTC', $limitPerEvent = 300)
    {
        $events = [];
        foreach($this->data as $event) {
            $title       = $event['title'];
            /** @var \App\Schedule $schedule */
            $schedule    = $event['schedule'];
            if ($this->onlyStartTime) {
                $occurrences = $schedule->getOccurrencesStartingBetween($start_date, $end_date, $limitPerEvent);
            }
            else {
                $occurrences = $schedule->getOccurrencesBetween($start_date, $end_date, $limitPerEvent);
            }
            $events = array_merge($events, array_map(function ($date) use ($schedule, $title) {
                $end = clone $date;
                $end->add(new \DateInterval('PT' . $schedule->duration . 'M'));

                // checked in logic
                $now = Carbon::now();
                $diff = $now->diffInMinutes(Carbon::instance($date));
                $checked_in = ($diff < ($schedule->duration) * 1.2) && in_array($schedule->id, $this->activeSchedules);

                return [
                    'schedule_id' => $schedule->id,
                    'title'       => $title,
                    'start'       => $date,
                    'end'         => $end,
                    'duration'    => $schedule->duration,
                    'checked_in'  => $checked_in,
                    'client_id'   => $schedule->client_id,
                    'caregiver_id'=> $schedule->caregiver_id,
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
