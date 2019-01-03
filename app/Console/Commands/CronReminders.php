<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Schedule;
use Illuminate\Support\Carbon;
use App\Notifications\Caregiver\ShiftReminder;
use App\TriggeredReminder;

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
            
        foreach ($schedules as $schedule) {
            if (TriggeredReminder::forReminder(ShiftReminder::getKey(), $schedule->id)->exists()) {
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
        
    }

    /**
     * Find any shifts past end time and notify the related Caregivers.
     *
     * @return void
     */
    public function overdueClockOuts()
    {
        
    }
}
