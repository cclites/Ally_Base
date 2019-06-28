<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Schedule;
use Illuminate\Support\Carbon;
use App\Notifications\Caregiver\ShiftReminder;
use App\TriggeredReminder;
use App\Notifications\Caregiver\ClockInReminder;
use App\Notifications\Caregiver\ClockOutReminder;
use App\Shift;
use App\Business;

class CronReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers notification for both custom and system reminders.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // ======================================
        // OFFICE USER REMINDERS
        // ======================================

        // ======================================
        // CAREGIVER REMINDERS
        // ======================================

        foreach (Business::all() as $business) {
            $this->upcomingShifts($business);

            $this->overdueClockins($business);

            $this->overdueClockOuts($business);
        }
    }

    /**
     * Find any upcoming shifts and notify the related Caregivers.
     *
     * @param \App\Business $business
     * @return void
     */
    public function upcomingShifts(Business $business) : void
    {
        $from = Carbon::now()->tz($business->timezone);
        $to = Carbon::now()->addMinutes(20)->tz($business->timezone);

        $schedules = Schedule::forBusinesses([$business->id])
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('caregiver_id')
            ->get();

        $triggered = TriggeredReminder::getTriggered(ShiftReminder::getKey(), $schedules->pluck('id'));

        foreach ($schedules as $schedule) {
            if (! $schedule->caregiver->active) {
                // ignore inactive caregivers (shouldn't be on the schedule but just in case)
                continue;
            }

            if ($triggered->contains($schedule->id)) {
                continue;
            }

            \Notification::send($schedule->caregiver->user, new ShiftReminder($schedule));

            TriggeredReminder::markTriggered(ShiftReminder::getKey(), $schedule->id);
        }
    }

    /**
     * Find any shifts past start time and notify the related Caregivers.
     *
     * @param \App\Business $business
     * @return void
     */
    public function overdueClockins(Business $business) : void
    {
        $from = Carbon::now()->subMinutes(60)->tz($business->timezone);
        $to = Carbon::now()->subMinutes(20)->tz($business->timezone);

        $schedules = Schedule::forBusinesses([$business->id])
            ->has('caregiver')
            ->with('shifts')
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('caregiver_id')
            ->get();

        $triggered = TriggeredReminder::getTriggered(ClockInReminder::getKey(), $schedules->pluck('id'));

        foreach ($schedules as $schedule) {
            if ($triggered->contains($schedule->id)) {
                continue;
            }

            if ($schedule->getShiftStatus() != Schedule::SCHEDULED) {
                // schedule has a shift attached, which means it has been clocked in already
                continue;
            }

            if (! $schedule->caregiver->active) {
                // ignore inactive caregivers (shouldn't be on the schedule but just in case)
                continue;
            }

            \Notification::send($schedule->caregiver->user, new ClockInReminder($schedule));

            TriggeredReminder::markTriggered(ClockInReminder::getKey(), $schedule->id);
        }
    }

    /**
     * Find any shifts past end time and notify the related Caregivers.
     *
     * @param \App\Business $business
     * @return void
     */
    public function overdueClockOuts(Business $business) : void
    {
        $shifts = Shift::forBusinesses([$business->id])
            ->where('status', Shift::CLOCKED_IN)
            ->whereNotNull('caregiver_id')
            ->get();

        $triggered = TriggeredReminder::getTriggered(ClockOutReminder::getKey(), $shifts->pluck('id'));

        foreach ($shifts as $shift) {
            if (empty($shift->schedule)) {
                continue;
            }

            if ($triggered->contains($shift->id)) {
                continue;
            }

            if (! $shift->caregiver->active) {
                // ignore inactive caregivers (shouldn't be clocked in but just in case)
                continue;
            }

            $from = Carbon::now()->subMinutes(60)->tz($business->timezone);
            $to = Carbon::now()->subMinutes(20)->tz($business->timezone);
            if ($shift->scheduledEndTime()->between($from, $to)) {
                \Notification::send($shift->caregiver->user, new ClockOutReminder($shift));

                TriggeredReminder::markTriggered(ClockOutReminder::getKey(), $shift->id);
            }
        }
    }
}
