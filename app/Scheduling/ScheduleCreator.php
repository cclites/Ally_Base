<?php

namespace App\Scheduling;

use App\Exceptions\InvalidScheduleParameters;
use App\Schedule;

class ScheduleCreator
{
    protected $aliases = [
        'selected_date' => 'start_date',
    ];

    protected $data = [];


    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Make the model but do not persist to the database
     *
     * @param array $attributes
     *
     * @return \App\Schedule
     */
    public function make($attributes = [])
    {
        return new Schedule(array_merge($this->getData(), $attributes));
    }

    /**
     * Create the model and store in the database
     *
     * @param array $attributes
     *
     * @return \App\Schedule|false
     */
    public function create($attributes = [])
    {
        $schedule = $this->make($attributes);
        if ($schedule->save()) {
            return $schedule;
        }
        return false;
    }

    /**
     * Copy an existing schedule and recreate with new parameters
     *
     * @param \App\Schedule $schedule
     * @param array $attributes
     *
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function recreate(Schedule $schedule, $attributes = [])
    {
        $newSchedule = $schedule->replicate();
        $newSchedule->fill(array_merge($this->getData(), $attributes));
        if ($newSchedule->save()) {
            return $newSchedule;
        }
        return false;
    }

    public function hasChangesFrom(Schedule $schedule)
    {
        $schedule = clone $schedule;
        $schedule->fill(array_merge($this->getData(), [
            // These variables always change, so ignore them
            'start_date' => $schedule->start_date
        ]));
        return $schedule->isDirty();
    }

    public function getData()
    {
        $this->handleAliases();
        $this->checkRequired(['start_date', 'end_date', 'time', 'duration', 'interval_type']);
        if (!$rule = $this->generateRule()) {
            throw new InvalidScheduleParameters('Unable to generate rule.');
        }
        $this->validateDuration();
        $this->validateStartDate();

        return [
            'start_date' => $this->data['start_date'],
            'end_date' => $this->data['end_date'],
            'time' => $this->data['time'],
            'duration' => $this->data['duration'],
            'rrule' => $rule,
            'notes' => $this->data['notes'] ?? null,
        ];
    }

    protected function handleAliases()
    {
        foreach ($this->aliases as $alias => $actual) {
            if (
                in_array($alias, array_keys($this->data))
                && ! in_array($actual, array_keys($this->data))
            ) {
                $this->data[$actual] = $this->data[$alias];
                unset($this->data[$alias]);
            }
        }
    }

    protected function checkRequired($required = [])
    {
        foreach($required as $field) {
            if (empty($this->data[$field])) {
                throw new InvalidScheduleParameters($field . ' is required for schedule generation.');
            }
        }
    }

    protected function generateRule()
    {
        $generator = new RuleGenerator();
        $intervalType = $this->data['interval_type'];
        if (in_array($intervalType, ['weekly', 'biweekly'])) {
            $this->checkRequired(['bydays']);
            return $generator->setIntervalType($intervalType)
                             ->byday(implode(',', $this->data['bydays']))
                             ->getRule();
        }
        else if (in_array($intervalType, ['monthly', 'bimonthly', 'quarterly', 'semiannually', 'annually'])) {
            $date = $this->data['start_date'];
            $dayOfMonth = (new \DateTime($date))->format('j');
            if (empty($bymonthdays)) {
                $bymonthdays = [$dayOfMonth];
            }
            return $generator->setIntervalType($intervalType)
                              ->bymonthday(implode(',', $bymonthdays))
                              ->getRule();
        }
    }

    protected function validateDuration() {
        $this->data['duration'] = intval($this->data['duration']);
        if (!$this->data['duration'] > 0) {
            throw new InvalidScheduleParameters('Duration must be an integer greater than 0.');
        }
    }

    /**
     * Start date must exist in the generated schedule
     */
    protected function validateStartDate()
    {
        if (in_array($this->data['interval_type'], ['weekly', 'biweekly'])) {
            $daysOfWeek = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'];
            $bydays     = array_map('strtolower', $this->data['bydays']);
            $weekdayNo  = (int)(new \DateTime($this->data['start_date']))->format('w');
            $weekdayId  = $daysOfWeek[$weekdayNo];
            $i=0;
            while ( ! in_array($weekdayId, $bydays)) {
                $i++;
                $weekdayNo += $i;
                if ($weekdayNo >= 7) {
                    $weekdayNo -= 7;
                }
                $weekdayId = $daysOfWeek[$weekdayNo];
            }
            if ($i>0) {
                $this->data['start_date'] = (new \DateTime($this->data['start_date']))
                    ->add(new \DateInterval('P' . $i . 'D'))
                    ->format('Y-m-d');
            }
        }
    }
}
