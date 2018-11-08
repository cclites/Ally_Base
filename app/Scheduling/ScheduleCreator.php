<?php

namespace App\Scheduling;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\MaximumWeeklyHoursExceeded;
use App\Schedule;
use App\ScheduleNote;
use Carbon\Carbon;

class ScheduleCreator
{
    /**
     * @var Carbon
     */
    protected $startsAt;

    /**
     * @var int|ScheduleNote
     */
    protected $note;

    /**
     * @var int
     */
    protected $carePlan;

    /**
     * @var string
     */
    protected $rrule;

    /**
     * @var string
     */
    protected $endingDate;

    /**
     * @var bool
     */
    protected $overrideMaxHours = false;

    /**
     * @var int
     */
    protected $maxHours;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \App\Scheduling\RuleGenerator
     */
    protected $ruleGenerator;

    /**
     * @var \App\Scheduling\RuleParser
     */
    protected $ruleParser;

    /**
     * @var \App\Scheduling\ScheduleAggregator
     */
    protected $scheduleAggregator;

    public function __construct(RuleGenerator $ruleGenerator, RuleParser $ruleParser, ScheduleAggregator $aggregator)
    {
        $this->ruleGenerator = $ruleGenerator;
        $this->ruleParser = $ruleParser;
        $this->scheduleAggregator = $aggregator;
    }

    /**
     * Set the date and time that the first shift begins
     *
     * @param \Carbon\Carbon $date
     * @return $this
     */
    public function startsAt(Carbon $date)
    {
        $this->startsAt = $date;
        return $this;
    }

    /**
     * Set the duration in minutes for each shift of the created schedules
     *
     * @param int $duration
     * @return $this
     */
    public function duration(int $duration)
    {
        $this->data['duration'] = $duration;
        return $this;
    }

    /**
     * Set the amount of overtime in minutes for each shift, if duration is null, the full shift duration is used
     * Note: this cannot be combined with holiday
     *
     * @param int|null $duration
     * @throws \Exception
     * @return $this
     */
    public function overtime(int $duration = null)
    {
        if ($duration === null && !$this->data['duration']) {
            throw new \Exception('ScheduleCreator: overtime must be declared after duration()');
        }
        if ($duration === null) {
            $duration = $this->data['duration'];
        }
        $this->data['overtime_duration'] = $duration;
        $this->data['hours_type'] = 'overtime';
        return $this;
    }

    /**
     * Set the amount of holiday time in minutes for each shift, if duration is null, the full shift duration is used
     * Note: this cannot be combined with overtime
     *
     * @param int|null $duration
     * @throws \Exception
     * @return $this
     */
    public function holiday(int $duration = null)
    {
        if ($duration === null && !$this->data['duration']) {
            throw new \Exception('ScheduleCreator: holiday must be declared after duration()');
        }
        if ($duration === null) {
            $duration = $this->data['duration'];
        }
        $this->data['overtime_duration'] = $duration;
        $this->data['hours_type'] = 'holiday';
        return $this;
    }

    /**
     * Set the assignments for the created schedules
     *
     * @param int $business_id
     * @param int $client_id
     * @param int|null $caregiver_id
     * @return $this
     */
    public function assignments(int $business_id, int $client_id, int $caregiver_id = null)
    {
        $this->data = array_merge($this->data, compact('business_id', 'client_id', 'caregiver_id'));
        return $this;
    }

    /**
     * Set the rates for the created schedules
     *
     * @param int $caregiver_rate
     * @param int $provider_fee
     * @param bool $fixed_rates
     * @return $this
     */
    public function rates($caregiver_rate = 0, $provider_fee = 0, $fixed_rates = false)
    {
        $this->data = array_merge($this->data, compact('caregiver_rate', 'provider_fee', 'fixed_rates'));
        return $this;
    }

    /**
     * Set the note to be attached to the created schedules
     *
     * @param int|\App\ScheduleNote $note
     * @return $this
     */
    public function attachNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Set the care plan to be attached to the created schedules.
     *
     * @param int $care_plan_id
     * @return $this
     */
    public function attachCarePlan($care_plan_id)
    {
        $this->data = array_merge($this->data, compact('care_plan_id'));
        return $this;
    }

    /**
     * Set the recurring interval for the schedule
     *
     * @param string $intervalType
     * @param \Carbon\Carbon $endingDate
     * @param array $byDays
     * @return $this
     * @throws \App\Exceptions\InvalidScheduleParameters
     * @throws \Exception
     */
    public function interval($intervalType, Carbon $endingDate, $byDays = [])
    {
        $this->endingDate = $endingDate;

        if (in_array($intervalType, ['weekly', 'biweekly'])) {
            if (empty($byDays)) {
                throw new InvalidScheduleParameters('By days is required for recurring weekly intervals.');
            }
            $this->rrule = $this->ruleGenerator->setIntervalType($intervalType)
                                               ->byday(implode(',', $byDays))
                                               ->getRule();
            return $this;
        }

        if (!$this->startsAt) {
            throw new \Exception('ScheduleCreator: startsAt must be called before interval');
        }
        $byMonthDay = $this->startsAt->format('j');
        $this->rrule = $this->ruleGenerator->setIntervalType($intervalType)
                                           ->bymonthdays($byMonthDay)
                                           ->getRule();
        return $this;
    }

    /**
     * Ignore the max weekly hours limit
     *
     * @param bool $enable
     * @return $this
     */
    public function overrideMaxHours($enable = true)
    {
        $this->overrideMaxHours = $enable;
        return $this;
    }

    /**
     * Create the schedules from the provided data and return a collection
     *
     * @return \Illuminate\Support\Collection|\App\Schedule[]
     * @throws \App\Exceptions\InvalidScheduleParameters
     */
    public function create()
    {
        $this->checkRequired(['duration', 'business_id', 'client_id']);
        $this->validateDuration();

        $occurrences = $this->generateOccurrences();
        $this->validateStartDate($occurrences);

        $schedules = $this->createSchedulesFromOccurrences($occurrences);
        $this->attachNoteToSchedules($schedules);

        return collect($schedules);
    }

    protected function checkRequired($required = [])
    {
        if (!$this->startsAt) {
            throw new InvalidScheduleParameters('startsAt is required for schedule generation.');
        }
        foreach ($required as $field) {
            if (empty($this->data[$field])) {
                throw new InvalidScheduleParameters($field . ' is required for schedule generation.');
            }
        }
    }

    protected function validateDuration()
    {
        $this->data['duration'] = intval($this->data['duration']);
        if ($this->data['duration'] <= 0) {
            throw new InvalidScheduleParameters('Duration must be an integer greater than 0.');
        }
    }

    protected function validateMaxHours(Carbon $date)
    {
        if ($this->overrideMaxHours) {
            return;
        }

        if (!strlen($this->maxHours)) {
            $this->maxHours = Client::find($this->data['client_id'])->max_weekly_hours ?? 999;
        }

        $totalHours = $this->scheduleAggregator->getTotalScheduledHoursForWeekOf($date, $this->data['client_id']);

        if ($totalHours > $this->maxHours) {
            throw new MaximumWeeklyHoursExceeded('The week of ' . $date->toDateString() . ' exceeds the maximum allowed hours for this client.');
        }
    }

    /**
     * Start date must exist in the generated schedule
     */
    protected function validateStartDate($occurrences)
    {
        if (!in_array($this->startsAt, $occurrences)) {
            throw new InvalidScheduleParameters('The schedule start date does not exist within the recurring periods.');
        }
    }

    protected function generateOccurrences()
    {
        if (!$this->rrule) {
            return [$this->startsAt];
        }

        $endingDate = $this->endingDate;
        $endsAt = $this->startsAt->copy()
                                 ->setDate($endingDate->format('Y'), $endingDate->format('n'), $endingDate->format('j'))
                                 ->addMinute();
        return $this->ruleParser->setRule($this->startsAt, $this->rrule)
                                ->getOccurrencesBetween($this->startsAt, $endsAt, 730);
    }

    protected function createSchedulesFromOccurrences($occurrences)
    {
        $schedules = [];
        \DB::beginTransaction();

        try {
            foreach ($occurrences as $date) {
                $schedules[] = Schedule::create(
                    array_merge(
                        $this->data,
                        [
                            'starts_at' => $date, // keep in business timezone
                            'weekday'   => $date->format('w'),
                        ]
                    )
                );
                $this->validateMaxHours(Carbon::instance($date));
            }
            \DB::commit();
            return $schedules;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    protected function attachNoteToSchedules($schedules)
    {
        if ($this->note) {
            foreach ($schedules as $schedule) {
                $schedule->attachNote($this->note);
            }
        }
    }
}
