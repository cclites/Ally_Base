<?php

namespace App\Console\Commands;

use App\BusinessChain;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class MigrateScheduleGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:schedule_groups {chain_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate a business chain to enable schedule groups';

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
        $chain = BusinessChain::findOrFail($this->argument('chain_id'));

        if ($chain->enable_schedule_groups) {
            $this->error('Schedule groups are already enabled.');
            return false;
        }

        if (!$this->confirm("Are you sure you wish to enable schedule groups for {$chain->name}?  Note: This will disable the bulk updater.")) {
            return;
        }

        DB::beginTransaction();

        $chain->enable_schedule_groups = true;
        $chain->save();

        $ids = $chain->businesses()->pluck('id')->implode(',');
        $query = "SELECT business_id, client_id, caregiver_id, client_rate, caregiver_rate, fixed_rates, care_plan_id, note_id, TIME(starts_at) as time, duration FROM schedules WHERE business_id IN ($ids) GROUP BY business_id, client_id, caregiver_id, TIME(starts_at), duration, client_rate, caregiver_rate, fixed_rates, care_plan_id, note_id HAVING count(*) > 1";
        $results = DB::select($query);
        foreach($results as $result) {
            $matching = [
                'client_id' => $result->client_id,
                'caregiver_id' => $result->caregiver_id,
                'client_rate' => $result->client_rate,
                'caregiver_rate' => $result->caregiver_rate,
                'fixed_rates' => $result->fixed_rates,
                'care_plan_id' => $result->care_plan_id,
                'note_id' => $result->note_id,
                'duration' => $result->duration,
            ];

            $query = DB::table('schedules')->where($matching)->whereRaw('TIME(starts_at) = ?', $result->time);

            $timezone = \App\Businesses\Timezone::getTimezone($result->business_id);

            $firstMatches = (clone $query)->limit(8)->get()->sortBy('created_at');
            $count = $firstMatches->count();
            $firstDate = Carbon::parse($firstMatches->first()->starts_at, $timezone);
            $lastDate = Carbon::parse($firstMatches->first()->starts_at, $timezone);

            $endDate = Carbon::parse((clone $query)->latest()->first()->starts_at, $timezone)->toDateString();
            $byDay = array_keys($firstMatches->reduce(function($carry, $row) use ($timezone) {
                $startsAt = Carbon::parse($row->starts_at, $timezone);
                $day = substr($startsAt->format('D'), 0, 2);
                $carry[$day] = 1;
                return $carry;
            }, []));
            $interval = $count > 2 && ($lastDate->diffInMonths($firstDate)) >= $count-1 ? 'monthly' : 'weekly';
            $rrule = sprintf("FREQ=%s;INTERVAL=1;BYDAY=%s", strtoupper($interval), strtoupper(implode(',', $byDay)));

            $group = \App\ScheduleGroup::create([
                'starts_at' => $firstDate->toDateTimeString(),
                'end_date' => $endDate,
                'rrule' => $rrule,
                'interval_type' => $interval,
            ]);

            (clone $query)->update(['group_id' => $group->id]);
        }

        DB::commit();
    }
}
