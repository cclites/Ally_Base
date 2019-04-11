<?php

namespace App\Scheduling;

use App\Schedule;

/**
 * Class ScheduleWarningAggregator
 * @package App\Scheduling
 */
class ScheduleWarningAggregator
{
    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $warnings;

    /**
     * ScheduleWarningAggregator constructor.
     * @param Schedule $schedule
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->warnings = collect([]);
    }

    /**
     * Run all warning checks and return the warnings
     * as an array of strings.
     *
     * @return array
     */
    public function getAll() : array
    {
        $this->checkCaregiverRestrictions();

        return $this->warnings->toArray();
    }

    /**
     * Check if the selected Caregiver has free text restrictions.
     *
     * @return bool
     */
    public function checkCaregiverRestrictions()
    {
        // check for any caregiver restrictions
        if (empty($this->schedule->caregiver->restrictions)) {
            return false;
        }

        $this->pushWarnings(
            $this->schedule->caregiver->restrictions->pluck('description')
        );

        return true;
    }

    /**
     * Append the warnings collection with the given data.
     *
     * @param iterable $warnings
     */
    public function pushWarnings(iterable $warnings) : void
    {
        foreach ($warnings as $warning) {
            $this->warnings = $this->warnings->push($warning);
        }
    }
}
