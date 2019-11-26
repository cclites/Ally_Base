<?php

namespace App\Console\Commands;

use App\Business;
use App\Reports\ScheduledPaymentsReport;
use App\Schedule;
use App\Shifts\ShiftCostCalculator;
use App\Scheduling\ScheduleAggregator;
use App\Scheduling\ScheduleCostCalculator;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduledShiftsCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:scheduled_shifts {start} {end} {--business_id=}  {--output=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CSV output for the scheduled payments';

    protected $csvSeparator = ';';  // Needs semi-colon because comma is used in names

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
        $start_date = date('Y-m-d', strtotime($this->argument('start')));
        $end_date = date('Y-m-d', strtotime($this->argument('end')));
        $start_time = '00:00:00';
        $end_time = '23:59:59';

        $business = Business::findOrFail($this->option('business_id'));

        $aggregator = new ScheduleAggregator();
        foreach($business->schedules as $schedule) {
            $clientName = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $caregiverName = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            $title = $clientName . ' (' . $caregiverName . ')';
            $aggregator->add($title, $schedule);
        }

        $activeSchedules = $business->shifts()->whereNull('checked_out_time')->pluck('schedule_id')->toArray();
        $aggregator->addActiveSchedules($activeSchedules);
        $events = $aggregator->events($start_date, $end_date);

        // Filter out unassigned events
        $events = array_filter($events, function($event) {
            $schedule = Schedule::with(['client', 'caregiver'])->find($event['schedule_id']);
            return ($schedule->client && $schedule->caregiver);
        });

        // Map events to CSV columns
        $rows = array_map(function($event) {
            $schedule = Schedule::with(['client', 'caregiver'])->find($event['schedule_id']);
            $clocked_in_date = Carbon::instance($event['start']);
            $clocked_out_date = Carbon::instance($event['end']);
            $duration = round($schedule->duration / 60, 2);
            $calculator = new ScheduleCostCalculator($schedule);
            return [
                'day' => $clocked_in_date->format('m/d/Y'),
                'schedule_start' => $clocked_in_date->format('g:i A'),
                'scheduled_end' => $clocked_out_date->format('g:i A'),
                'hours' => $duration,
                'client' => $schedule->client->name(),
                'caregiver' => $schedule->caregiver->name(),
                'caregiver_rate' => $schedule->getCaregiverRate(),
                'provider_fee' => $schedule->getProviderFee(),
                'total_caregiver_rate' => $calculator->getCaregiverCost(),
                'total_provider_fee' => $calculator->getProviderFee(),
                'total_ally_fee' => $calculator->getAllyFee(),
                'total_fee' => $calculator->getTotalCost(),
                'payment_type' => str_replace('App\\', '', $schedule->client->default_payment_type),
            ];
        }, $events);

        $rows = array_values($rows);

        $csv = implode($this->csvSeparator, array_keys($rows[0])) . "\n";
        foreach($rows as $row) {
            $csv .= implode($this->csvSeparator, $row) . "\n";
        }

        if ($this->option('output')) {
            return file_put_contents($this->option('output'), $csv);
        }
        echo "\n";
        echo $csv;
    }
}
