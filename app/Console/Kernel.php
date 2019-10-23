<?php

namespace App\Console;

use App\Console\Commands\AchOfflineChargeCommand;
use App\Console\Commands\CronChargePaymentNotifications;
use App\Console\Commands\CronHhaCheckStatus;
use App\Console\Commands\CronScheduleConverter;
use App\Console\Commands\CronUpdateTransactionLog;
use App\Console\Commands\ImportGenerationsCaregivers;
use App\Console\Commands\ImportPaychexCaregivers;
use App\Console\Commands\ScheduledPaymentsCsv;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CronShiftSummaryEmails;
use App\Console\Commands\CronDailyNotifications;
use App\Console\Commands\CronReminders;
use App\Console\Commands\CronFlushTriggeredReminders;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ScheduledPaymentsCsv::class,
        ImportGenerationsCaregivers::class,
        ImportPaychexCaregivers::class,
        CronUpdateTransactionLog::class,
        CronScheduleConverter::class,
        CronShiftSummaryEmails::class,
        CronDailyNotifications::class,
        CronReminders::class,
        CronFlushTriggeredReminders::class,
        AchOfflineChargeCommand::class,
        CronHhaCheckStatus::class,
        CronChargePaymentNotifications::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cron:transaction_log')
            ->dailyAt('12:00'); // 7am EST / 8am EDT

        $schedule->command('cron:schedule_converter')
            ->hourly();

        $schedule->command('cron:shift_summary_emails')
            ->weeklyOn(1, '20:00'); // 3:00pm EST / 4pm EDT

        $schedule->command('cron:daily_notifications')
            ->dailyAt('13:59'); // 8:59am EST / 9:59 EDT

        // TEMPORARILY DISABLED
//        $schedule->command('cron:visit_accuracy')
//            ->weeklyOn(1, '18:00'); // Mondays @ 1:00pm EST

        $schedule->command('cron:reminders')
            ->everyMinute()
            ->withoutOverlapping();
            
        $schedule->command('cron:flush_reminders')
            ->twiceDaily(8, 20)
            ->withoutOverlapping();

        $schedule->command(CronHhaCheckStatus::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        $schedule->command('cron:charge_payment_notifications')
            ->dailyAt('23:30'); // 6:30 PM EST / 7:30 EDT
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
