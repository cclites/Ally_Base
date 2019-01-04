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
use App\Caregiver;

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
        
        $this->upcomingShifts();

        $this->overdueClockins();
        
        $this->overdueClockOuts();

        // ======================================
        // MULTI-USER REMINDERS
        // ======================================
        
        $this->expiringCertifications();

        $this->expiredCertifcations();
    }

    /**
     * Find any upcoming shifts and notify the related Caregivers.
     *
     * @return void
     */
    public function upcomingShifts()
    {
        $schedules = Schedule::whereBetween('starts_at', [Carbon::now(), Carbon::now()->addMinutes(20)])
            ->get();
        
        $triggered = TriggeredReminder::forReminder(ShiftReminder::getKey())
            ->whereIn('reference_id', $schedules->pluck('id'))
            ->get();

        foreach ($schedules as $schedule) {
            if ($triggered->where('reference_id', $schedule->id)->count() > 0) {
                continue;
            }

            \Notification::send($schedule->caregiver->user, new ShiftReminder($schedule));

            TriggeredReminder::create([
                'reference_id' => $schedule->id,
                'notification' => ShiftReminder::getKey(),
            ]);
        }
    }

    /**
     * Find any shifts past start time and notify the related Caregivers.
     *
     * @return void
     */
    public function overdueClockins()
    {
        $schedules = Schedule::with('shifts')
            ->whereBetween('starts_at', [Carbon::now()->addMinutes(20), Carbon::now()->addMinutes(60)])
            ->get();

        $triggered = TriggeredReminder::forReminder(ClockInReminder::getKey())
            ->whereIn('reference_id', $schedules->pluck('id'))
            ->get();

        foreach ($schedules as $schedule) {
            if ($triggered->where('reference_id', $schedule->id)->count() > 0) {
                continue;
            }

            if ($schedule->shift_status != Schedule::SCHEDULED) {
                // schedule has a shift attached, which means it has been clocked in already
                continue;
            }

            \Notification::send($schedule->caregiver->user, new ClockInReminder($schedule));

            TriggeredReminder::create([
                'reference_id' => $schedule->id,
                'notification' => ClockInReminder::getKey(),
            ]);
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

        $triggered = TriggeredReminder::forReminder(ClockOutReminder::getKey())
            ->whereIn('reference_id', $shifts->pluck('id'))
            ->get();

        foreach ($shifts as $shift) {
            if (empty($shift->schedule)) {
                continue;
            }

            if ($triggered->where('reference_id', $shift->id)->count() > 0) {
                continue;
            }
            
            $start = Carbon::now()->subMinutes(60)->setTimezone('UTC');
            $end = Carbon::now()->subMinutes(20)->setTimezone('UTC');
            if ($shift->scheduledEndTime()->setTimezone('UTC')->between($start, $end)) {
                \Notification::send($shift->caregiver->user, new ClockOutReminder($shift));

                TriggeredReminder::create([
                    'reference_id' => $shift->id,
                    'notification' => ClockOutReminder::getKey(),
                ]);
            }
        }
    }

    /**
     * Find any Caregiver certifications that are expiring soon.
     *
     * @return void
     */
    public function expiringCertifications()
    {
    }

    /**
     * Find any Caregiver certifications that have expired.
     *
     * @return void
     */
    public function expiredCertifcations()
    {
    }
}
