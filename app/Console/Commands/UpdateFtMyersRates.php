<?php

namespace App\Console\Commands;

use App\Schedule;
use App\Shift;
use Illuminate\Console\Command;

class UpdateFtMyersRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:ftmyerrates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One time command to update the rates in Ft Myers schedule and shifts';

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
        $shifts = Shift::where('business_id', 5)->where('checked_in_time', '>', '2017-11-13 05:00:00')->get();
        foreach($shifts as $shift) {
            $this->updateRate($shift);
        }

        $schedules = Schedule::where('business_id', 5)->get();
        foreach($schedules as $schedule) {
            $this->updateRate($schedule);
        }
    }

    protected function updateRate($model)
    {
        if (!$client = $model->client) {
            return;
        }

        if (!$caregiver = $client->caregivers->where('id', $model->caregiver_id)->first()) {
            return;
        }

        if (!$caregiver->pivot) {
            return;
        }

        if (!$caregiver->pivot->provider_hourly_fee) {
            return;
        }

        if ($model->provider_fee == $caregiver->pivot->provider_hourly_fee) {
            return;
        }

        $providerDiff = $model->provider_fee - $caregiver->pivot->provider_hourly_fee;
        $pctDiff = $providerDiff / $caregiver->pivot->provider_hourly_fee;
        if ($pctDiff <= 0.05) {
            $this->output->writeln("Updating fee from " . $model->provider_fee . " to " . $caregiver->pivot->provider_hourly_fee);
            $model->provider_fee = $caregiver->pivot->provider_hourly_fee;
            $model->save();
        }
    }
}
