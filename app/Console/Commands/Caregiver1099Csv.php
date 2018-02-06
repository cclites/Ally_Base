<?php

namespace App\Console\Commands;

use App\Caregiver;
use App\Client;
use App\Shift;
use Illuminate\Console\Command;

class Caregiver1099Csv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:caregiver_1099 {--output=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate caregiver 1099 data';

    /**
     * @var string
     */
    protected $csvSeparator = ',';

    /**
     * @var int
     */
    protected $year;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->year = date('Y') - 1;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rows = [];

        // Get clients that are LTCI or Provider Pay
        $clients = Client::where('client_type', 'LTCI')
            ->orWhere('default_payment_type', '=', 'App\\Business')
            ->get();

        foreach($clients as $client) {
            $shifts = $client->shifts()
                             ->with('caregiver')
                             ->whereHas('deposits', function($q) {
                                 $q->where('deposit_type', 'caregiver')
                                   ->whereYear('created_at', $this->year);
                             })->get();

            if (!$shifts->count()) {
                continue;
            }

            foreach($shifts->groupBy('caregiver_id') as $caregiverId => $caregiverShifts) {
                $caregiver = $caregiverShifts->first()->caregiver;
                $amount = 0;
                foreach($caregiverShifts as $shift) {
                    $amount = bcadd($amount, $shift->costs()->getCaregiverCost(), 2);
                }
                $address = $caregiver->addresses->first();
                $rows[] = [
                    'client_id' => $client->id,
                    'client_name' => $client->name(),
                    'caregiver_id' => $caregiver->id,
                    'caregiver_name' => $caregiver->name(),
                    'amount' => $amount,
                    'ssn' => $caregiver->ssn,
                    'address' => $address->address1,
                    'city' => $address->city,
                    'state' => $address->state,
                    'zip' => $address->zip,
                ];
            }
        }

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
