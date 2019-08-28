<?php

namespace App\Console\Commands;

use App\ShiftActivity;
use Illuminate\Console\Command;
use function Sentry\addBreadcrumb;

class CleanDuplicateActivitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:duplicate-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove any duplicate activities from shifts.';

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
     * @throws \Exception
     */
    public function handle()
    {
        $this->info("Searching for duplicate shift activities...");

        $results = ShiftActivity::from('shift_activities AS sa')
            ->with('shift', 'shift.activities')
            ->select(\DB::raw('shift_id, activity_id, (SELECT COUNT(*) FROM shift_activities WHERE activity_id = sa.activity_id AND shift_id = sa.shift_id) AS counter'))
            ->having('counter', '>', 1)
            ->get();

        $this->info("Found {$results->count()} duplicate shift activities, cleaning...");

        \DB::beginTransaction();

        $results->each(function (ShiftActivity $shiftActivity) {
            /** @var \App\Shift $shift */
            $shift = $shiftActivity->shift;
            if ($shift->activities->where('id', $shiftActivity->activity_id)->count() > 1) {
                $shift->activities()->detach($shiftActivity->activity_id);
                $shift->activities()->attach($shiftActivity->activity_id);
            }
        });

        \DB::commit();

        $this->info("All duplicate shift activities have been cleaned.");

        return 0;
    }
}
