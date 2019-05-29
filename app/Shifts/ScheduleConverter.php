<?php

namespace App\Shifts;

use App\Business;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Shift;
use App\Shifts\Data\ClockData;
use Carbon\Carbon;
use DateTimeZone;
use App\Events\ShiftFlagsCouldChange;

/**
 * Class ScheduleConverter
 * Converts schedules to actual shifts
 *
 * @package App\Shifts
 */
class ScheduleConverter
{
    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * @var \DateTimeZone
     */
    protected $timezone;

    /**
     * @var \App\Scheduling\ScheduleAggregator
     */
    protected $aggregator;

    /**
     * The Schedule statuses that should be allowed to be converted.
     *
     * @var array
     */
    public static $convertibleStatuses = [Schedule::OK, Schedule::ATTENTION_REQUIRED];
    
    public function __construct(Business $business, ScheduleAggregator $aggregator = null)
    {
        $this->business = $business;
        $this->aggregator = $aggregator ?? new ScheduleAggregator();
        $this->timezone = new DateTimeZone($business->timezone ?? 'America/New_York');
    }

    /**
     * Convert all schedules from the beginning of the week to 6 hours prior to run time
     *
     * @return array
     */
    public function convertAllThisWeek()
    {
        // Note: A change in logic here will need to be reflected in the
        // will_be_converted attribute of the Schedule model.
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $start = Carbon::now($this->timezone)->startOfWeek();
        $end = Carbon::now($this->timezone)->subHours(6);

        if (Carbon::now()->dayOfWeek === Carbon::MONDAY) {
            // If monday morning, still use last week
            $start->subWeek();
        }

        return $this->convertAllBetween($start, $end);
    }

    /**
     * Convert all schedules between a specified start and end date
     *
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return array
     */
    public function convertAllBetween(Carbon $start, Carbon $end)
    {
        $shifts = [];
        $schedules = $this->aggregator->where('business_id', $this->business->id)
                                      ->onlyStatus(self::$convertibleStatuses)
                                      ->getSchedulesStartingBetween($start, $end);

        $this->output("Converting schedules between {$start->toDateTimeString()} and {$end->toDateTimeString()}: ");

        foreach ($schedules as $schedule) {
            $expectedClockIn = $schedule->starts_at;
            if (
                !$this->shiftMatchesTime($schedule, $expectedClockIn)
                && !$this->hasBeenConverted($schedule)
            ) {
                $shift = $this->convert($schedule, $expectedClockIn);
                if ($shift) {
                    $shifts[] = $shift;
                }
            }
        }

        $this->output(count($shifts) . "shifts\n");

        return $shifts;
    }

    /**
     * Check if a shift already exists for scheduled time
     *
     * @param \App\Schedule $schedule
     * @param Carbon $date
     * @return bool
     */
    public function shiftMatchesTime(Schedule $schedule, Carbon $expectedClockIn)
    {
        // Use UTC when comparing against checked_in_time
        $expectedClockIn = $expectedClockIn->copy()->setTimezone('UTC');
        return Shift::where('client_id', $schedule->client_id)
                    ->where('caregiver_id', $schedule->caregiver_id)
                    ->whereBetween('checked_in_time', [
                        $expectedClockIn->copy()->subHours(2),
                        $expectedClockIn->copy()->addHours(2)
                    ])
                    ->exists();
    }

    /**
     * Check if this particular schedule and time has already been converted
     *
     * @param \App\Schedule $schedule
     * @param \Carbon\Carbon $expectedClockIn
     * @return bool
     */
    public function hasBeenConverted(Schedule $schedule)
    {
        return !is_null($schedule->converted_at) || $schedule->shifts()->exists();
    }

    /**
     * Convert a schedule to an actual shift for a specified clock in time
     *
     * @param \App\Schedule $schedule
     * @param \Carbon\Carbon $clockIn
     * @param string $status
     * @return Shift|false
     */
    public function convert(Schedule $schedule, Carbon $clockIn, $status = null)
    {
        if (empty($status)) {
            $status = Shift::WAITING_FOR_CONFIRMATION;

            if (app('settings')->get($schedule->business_id, 'auto_confirm')) {
                $status = Shift::WAITING_FOR_AUTHORIZATION;
            }
        }

        // Make sure schedule has proper assignments
        if ($schedule->business_id != $this->business->id) {
            return false;
        }
        if (!$schedule->caregiver_id) {
            return false;
        }
        if (!$schedule->client_id) {
            return false;
        }

        // Create Shift
        $clockIn = $clockIn->setTimezone('UTC');
        $clockOut = $clockIn->copy()->addMinutes($schedule->duration);

        $clockIn = new ClockData(Shift::METHOD_CONVERTED, $clockIn->toDateTimeString());
        $clockOut = new ClockData(Shift::METHOD_CONVERTED, $clockOut->toDateTimeString());

        $factory = ShiftFactory::withSchedule(
            $schedule,
            $clockIn,
            $clockOut,
            $status
        );
        $shift = $factory->create();

        if ($shift) {
            $schedule->update(['converted_at' => Carbon::now()]);

            event(new ShiftFlagsCouldChange($shift));
        }

        return $shift;
    }

    public function output($message)
    {
        if (config('app.env') == 'testing') {
            return;
        }

        echo $message;
    }
}
