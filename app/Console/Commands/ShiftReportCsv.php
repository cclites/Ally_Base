<?php

namespace App\Console\Commands;

use App\Reports\ScheduledPaymentsReport;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ShiftReportCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:shifts {start} {end} {--business_id=}  {--output=}';

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

        $query = Shift::with(['client', 'caregiver', 'business', 'schedule'])
            ->whereBetween('checked_in_time', [$start_date . ' ' . $start_time, $end_date . ' ' . $end_time]);
        if ($business_id = $this->option('business_id')) {
            $query->where('business_id', $business_id);
        }

        $shifts = $query->get();
        $rows = $shifts->map(function(Shift $shift) {
            $clocked_in_date = (new Carbon($shift->checked_in_time, 'UTC'))->setTimezone('America/New_York');
            $clocked_out_date = (new Carbon($shift->checked_out_time, 'UTC'))->setTimezone('America/New_York');
            $calculator = $shift->costs();
            return [
                'day' => $clocked_in_date->format('m/d/Y'),
                'clock_in_time' => $clocked_in_date->format('g:i A'),
                'clock_out_time' => $clocked_out_date->format('g:i A'),
                'hours' => $shift->duration(),
                'client' => $shift->client->name(),
                'caregiver' => $shift->caregiver->name(),
                'caregiver_rate' => $shift->caregiver_rate,
                'provider_fee' => $shift->provider_fee,
                'total_caregiver_rate' => $calculator->getCaregiverCost(),
                'total_provider_fee' => $calculator->getProviderFee(),
                'total_ally_fee' => $calculator->getAllyFee(),
                'total_fee' => $calculator->getTotalCost(),
                'payment_type' => str_replace('App\\', '', $shift->client->default_payment_type),
            ];
        });

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
