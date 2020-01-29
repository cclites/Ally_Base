<?php

namespace App\Console\Commands;

use App\Billing\Deposit;
use App\Billing\Payments\Methods\BankAccount;
use App\Exports\GenericExport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ACHDepositExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:deposit_export {date} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports a list of deposits from a specific date';

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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle()
    {
        $date = Carbon::parse($this->argument('date'));
        if ($date->year < 2018 || $date->year > Carbon::now()->year) {
            $this->error('Invalid date provided.');
            return false;
        }

        $file = $this->argument('file');
        if (!touch($file)) {
            $this->error('Unable to write to file: ' . $file);
            return false;
        }

        $filename = pathinfo($file, PATHINFO_FILENAME) . '.xlsx';
        $storagePath = storage_path('app' . DIRECTORY_SEPARATOR . $filename);

        $deposits = Deposit::with('transaction.method')->whereBetween('created_at', [$date->toDateString() . ' 00:00:00', $date->toDateString() . ' 23:59:59'])
            ->get()
            ->map(function(Deposit $deposit) {
                $method = $deposit->transaction ? $deposit->transaction->method : null;
                return [
                    'Deposit ID' => $deposit->id,
                    'Deposit Type' => $deposit->deposit_type,
                    'Caregiver ID' => $deposit->caregiver_id,
                    'Business ID' => $deposit->business_id,
                    'Successful' => $deposit->success ? 1 : 0,
                    'Amount' => $deposit->amount,
                    'Name on Account' => ($method instanceof BankAccount) ? $method->name_on_account : "",
                    'Routing Number' => ($method instanceof BankAccount) ? $method->routing_number : "",
                    'Account Number' => ($method instanceof BankAccount) ? $method->account_number : "",
                    'Account Type' => ($method instanceof BankAccount) ? $method->account_type : "",
                    'Account Holder Type' => ($method instanceof BankAccount) ? $method->account_holder_type : "",
                ];
            });

        Excel::store(new GenericExport($deposits), DIRECTORY_SEPARATOR . $filename);

        if (! file_exists($storagePath)) {
            throw new \Exception("Unable to write file to: " . $storagePath);
        }

        $this->output->writeln("Written {$deposits->count()} deposits to: $storagePath");
    }
}
