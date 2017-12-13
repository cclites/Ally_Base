<?php

namespace App\Console;

use App\Console\Commands\CronScheduleConverter;
use App\Console\Commands\CronUpdateTransactionLog;
use App\Console\Commands\ImportGenerationsCaregivers;
use App\Console\Commands\ImportPaychexCaregivers;
use App\Console\Commands\ScheduledPaymentsCsv;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
            ->dailyAt('18:00'); // 1PM EST / 2PM EDT
        $schedule->command('cron:schedule_converter')
            ->hourly();
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
