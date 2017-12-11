<?php

namespace App\Console\Commands;

use App\Business;
use App\Shifts\ScheduleConverter;
use Illuminate\Console\Command;

class CronScheduleConverter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:schedule_converter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert scheduled events to actual shifts for the current week of all businesses (cronjob)';

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
        $businesses = Business::has('schedules')->orderBy('id')->get();
        foreach($businesses as $business) {
            $converter = new ScheduleConverter($business);
            $converter->convertAllThisWeek();
        }
    }
}
