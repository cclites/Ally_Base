<?php

namespace App\Scheduling;

use App\Schedule;
use Carbon\Carbon;

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
        if (! empty($this->schedule->caregiver)) {
            $this->checkCaregiverRestrictions();
            $this->checkCaregiverDaysOff();
            $this->checkCaregiverLicenses();
        }

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

    public function checkCaregiverLicenses()
    {
        // check for expired/expiring caregiver licenses
        $expired = $this->schedule->caregiver->licenses()
            ->where('expires_at', '<', Carbon::now())
            ->get()
            ->map(function ($license) {
                $date = $license->expires_at->format('m/d/Y');
                return "{$this->schedule->caregiver->name}'s {$license->name} certification expired on $date.";
            });

        $expiring = $this->schedule->caregiver->licenses()
            ->whereBetween('expires_at', [Carbon::now(), Carbon::now()->addDays(30)])
            ->get()
            ->map(function ($license) {
                $date = $license->expires_at->format('m/d/Y');
                return "{$this->schedule->caregiver->name}'s {$license->name} certification expires on $date.";
            });

        if (empty($expired) && empty($expiring)) {
            return false;
        }

        $this->pushWarnings($expired);
        $this->pushWarnings($expiring);

        return true;
    }

    /**
     * Check if the Caregiver has marked the day off for any of
     * the dates during the scheduled shift.
     *
     * @return bool
     */
    public function checkCaregiverDaysOff()
    {
        $dateRange = [
            $this->schedule->starts_at->format('Y-m-d'),
            $this->schedule->getEndDateTime()->format('Y-m-d')
        ];

        $warnings = $this->schedule->caregiver->daysOff()
            ->whereBetween('date', $dateRange)
            ->get()
            ->map(function ($dayOff) {
                $date = Carbon::parse($dayOff->date)->format('m/d/Y');
                return "{$this->schedule->caregiver->name} has marked themselves unavailable on $date ({$dayOff->description}).";
            });

        if (empty($warnings)) {
            return false;
        }

        $this->pushWarnings($warnings);

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
