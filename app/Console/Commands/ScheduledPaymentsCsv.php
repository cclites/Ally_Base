<?php

namespace App\Console\Commands;

use App\Reports\ScheduledPaymentsReport;
use Illuminate\Console\Command;

class ScheduledPaymentsCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:scheduled  {--business_id=}  {--output=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CSV output for the scheduled payments';

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
        $report = new ScheduledPaymentsReport();
        if ($this->option('business_id')) {
            $report->where('business_id', $this->option('business_id'));
        }

        $rows = $report->rows();
        $rows = $rows->map(function($row) {
           $row += [
               'client_id' => $row['client_id'],
               'client_name' => $row['client_name'],
               'caregiver_id' => $row['caregiver_id'],
               'caregiver_name' => $row['caregiver_name'],
           ];
           unset($row['client']);
           unset($row['caregiver']);
           return $row;
        });

        $csv = implode(',', array_keys($rows[0])) . "\n";
        foreach($rows as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        if ($this->option('output')) {
            return file_put_contents($this->option('output'), $csv);
        }
        echo "\n";
        echo $csv;
    }
}
