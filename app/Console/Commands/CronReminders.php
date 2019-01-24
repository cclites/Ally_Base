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
        }


        $this->overdueClockOuts();
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

        $schedules = Schedule::whereBetween('starts_at', [$from, $to])
            ->get();

        $triggered = TriggeredReminder::getTriggered(ShiftReminder::getKey(), $schedules->pluck('id'));

        foreach ($schedules as $schedule) {
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

        $schedules = Schedule::with('shifts')
            ->whereBetween('starts_at', [$from, $to])
            ->get();

        $triggered = TriggeredReminder::getTriggered(ClockInReminder::getKey(), $schedules->pluck('id'));

        foreach ($schedules as $schedule) {
            if ($triggered->contains($schedule->id)) {
                continue;
            }

            if ($schedule->shift_status != Schedule::SCHEDULED) {
                // schedule has a shift attached, which means it has been clocked in already
                continue;
            }

            \Notification::send($schedule->caregiver->user, new ClockInReminder($schedule));

            TriggeredReminder::markTriggered(ClockInReminder::getKey(), $schedule->id);
        }
    }

    /**
     * Find any shifts past end time and notify the related Caregivers.
     *
     * @return void
     */
    public function overdueClockOuts()
    {
        $shifts = Shift::where('status', Shift::CLOCKED_IN)
            ->where('id', '99818')
            ->get();

        $triggered = TriggeredReminder::getTriggered(ClockOutReminder::getKey(), $shifts->pluck('id'));

        foreach ($shifts as $shift) {
            if (empty($shift->schedule)) {
                continue;
            }

            if ($triggered->contains($shift->id)) {
                continue;
            }

            $start = Carbon::now()->subMinutes(60)->setTimezone('UTC');
            $end = Carbon::now()->subMinutes(20)->setTimezone('UTC');
            if ($shift->scheduledEndTime()->setTimezone('UTC')->between($start, $end)) {
                \Notification::send($shift->caregiver->user, new ClockOutReminder($shift));

                TriggeredReminder::markTriggered(ClockOutReminder::getKey(), $shift->id);
            }
        }
    }
}
