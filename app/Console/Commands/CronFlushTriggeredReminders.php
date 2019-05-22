<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TriggeredReminder;
use Illuminate\Support\Carbon;

class CronFlushTriggeredReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:flush_reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean out reminders_triggered table based on expired_at timestamps.';

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
        TriggeredReminder::where('expires_at', '<', Carbon::now())
            ->delete();
    }
}
