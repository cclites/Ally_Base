<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Caregiver;
use App\Notifications\Caregiver\VisitAccuracyCheck;
use App\Shift;
use Illuminate\Support\Carbon;

class CronVisitAccuracyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:visit_accuracy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind active Caregivers to check their visits for accuracy.';

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
        // limit query to past 30 days to reduce stress on the DB
        $start = Carbon::now()->subDays(30)->setTimezone('UTC');
        $end = Carbon::now()->setTimezone('UTC');

        $users = Shift::whereBetween('checked_in_time', [$start, $end])
            ->with('caregiver', 'caregiver.user')
            ->whereUnconfirmed()
            ->get()
            ->pluck('caregiver.user');

        \Notification::send($users, new VisitAccuracyCheck());
    }
}
