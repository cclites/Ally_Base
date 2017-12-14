<?php
namespace App\Shifts;

use App\Business;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;
use DateTimeZone;

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

    public function __construct(Business $business)
    {
        $this->business = $business;
        $this->timezone = new DateTimeZone($business->timezone ?? 'America/New_York');
    }

    /**
     * Convert all schedules from the beginning of the week to 6 hours prior to run time
     *
     * @return array
     */
    public function convertAllThisWeek()
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $start = Carbon::now($this->timezone)->startOfWeek();
        $end = Carbon::now($this->timezone)->subHours(6);

        if (Carbon::now()->dayOfWeek === Carbon::MONDAY && Carbon::now()->hour < 12) {
            // If monday morning, still use last week
            $start->subWeek();
        }

        dd($start, $end);

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

        $events = $this->business->getEvents($start, $end, true);
        foreach($events as $event) {
            $schedule = Schedule::find($event['schedule_id']);
            $expectedClockIn = Carbon::instance($event['start']);
            if (!$this->shiftExistsFor($schedule, $expectedClockIn)) {
                $shift = $this->convert($schedule, $expectedClockIn);
                if ($shift) $shifts[] = $shift;
            }
        }

        return $shifts;
    }

    /**
     * Check if a shift already exists for a scheduled event
     *
     * @param \App\Schedule $schedule
     * @param Carbon $date
     * @return bool
     */
    public function shiftExistsFor(Schedule $schedule, Carbon $expectedClockIn)
    {
        return Shift::where(function($q) use ($schedule, $expectedClockIn) {
            $q->where('schedule_id', $schedule->id)
                ->whereBetween('checked_in_time', [$expectedClockIn->copy()->subHours(8), $expectedClockIn->copy()->addHours(8)]);
        })->orWhere(function($q) use ($schedule, $expectedClockIn) {
            $q->where('client_id', $schedule->client_id)
                ->where('caregiver_id', $schedule->caregiver_id)
                ->whereBetween('checked_in_time', [$expectedClockIn->copy()->subHours(2), $expectedClockIn->copy()->addHours(2)]);
        })->exists();
    }

    /**
     * Convert a schedule to an actual shift for a specified clock in time
     *
     * @param \App\Schedule $schedule
     * @param $date
     * @param string $status
     * @return Shift|false
     */
    public function convert(Schedule $schedule, Carbon $clockIn, $status = Shift::UNCONFIRMED)
    {
        // Make sure schedule has proper assignments
        if ($schedule->business_id !== $this->business->id) return false;
        if (!$schedule->caregiver_id) return false;
        if (!$schedule->client_id) return false;

        // Create Shift
        $start = $clockIn->setTimezone('UTC');
        $shift = Shift::create([
            'business_id' => $schedule->business_id,
            'caregiver_id' => $schedule->caregiver_id,
            'client_id' => $schedule->client_id,
            'checked_in_time' => $start,
            'checked_out_time' => $start->copy()->addMinutes($schedule->duration),
            'schedule_id' => $schedule->id,
            'hours_type' => $schedule->hours_type,
            'caregiver_rate' => $schedule->getCaregiverRate(),
            'provider_fee' => $schedule->getProviderFee(),
            'status' => $status,
        ]);

        return $shift;
    }

}