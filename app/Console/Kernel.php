<?php

namespace App\Console;

use App\Console\Commands\CronScheduleConverter;
use App\Console\Commands\CronUpdateTransactionLog;
use App\Console\Commands\ImportGenerationsCaregivers;
use App\Console\Commands\ImportPaychexCaregivers;
use App\Console\Commands\ScheduledPaymentsCsv;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CronShiftSummaryEmails;
use App\Console\Commands\CronDailyNotifications;

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
            ->weeklyOn(1, '14:30'); // 9:30am EST / 10:30am EDT

        $schedule->command('cron:daily_notifications')
            ->dailyAt('8:59'); // 8:59 EST / 9:59 EDT
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
