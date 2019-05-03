<?php

namespace App\Console\Commands;

use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\Deposit;
use App\Caregiver;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CaregiverDuplicateDeposit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'caregiver:duplicate_deposit {duplicate_deposit_date} {caregiver_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new invoice item reversing the duplicate deposit amount (ECS) - Made for overpayment on 4/25/19.';

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
        $duplicateDepositDate = Carbon::parse($this->argument("duplicate_deposit_date"))->setTime(20, 0);
        $amount = floatval($this->argument("amount"));
        $reversalAmount = $amount * -1;
        $caregiver = Caregiver::findOrFail($this->argument("caregiver_id"));
        $invoice = CaregiverInvoice::where('caregiver_id', $caregiver->id)->where('amount_paid', 0)->orderBy('id', 'DESC')->first();

        \DB::beginTransaction();

        if (!$invoice) {
            $this->error("No unprocessed invoice for this caregiver.");
            if (!$this->confirm("Create a new invoice for {$caregiver->name()}?")) {
                return false;
            }
            $invoice = CaregiverInvoice::create(['caregiver_id' => $caregiver->id, 'name' => CaregiverInvoice::getNextName($caregiver->id)]);
        }

        if (!$this->confirm("Are you sure you wish to add an invoice item of $reversalAmount to {$caregiver->name()}?")) {
            return false;
        }

        $invoice->addItem(new CaregiverInvoiceItem([
            'group' => 'Adjustments',
            'name' => 'Reversal of Duplicate Deposit',
            'notes' => 'A duplicate deposit of ' . number_format($amount, 2) . ' was made in error on ' . $duplicateDepositDate->format('m/d/Y'),
            'units' => 1,
            'rate' => $reversalAmount,
            'total' => $reversalAmount,
            'date' => Carbon::now(),
        ]));

        \DB::commit();

        $this->output->writeln("An invoice item for $reversalAmount was added to invoice {$invoice->getName()}");


        if (!$this->confirm("Do you want to record an invoice and deposit for the original overpayment?")) {
            return false;
        }

        \DB::beginTransaction();

        $duplicateInvoice = CaregiverInvoice::create(['caregiver_id' => $caregiver->id, 'name' => CaregiverInvoice::getNextName($caregiver->id)]);
        $duplicateInvoice->addItem(new CaregiverInvoiceItem([
            'group' => 'Adjustments',
            'name' => 'Record of Duplicate Deposit',
            'notes' => 'A duplicate deposit of ' . number_format($this->argument('amount'), 2) . ' was made in error on ' . $duplicateDepositDate->format('m/d/Y'),
            'units' => 1,
            'rate' => $amount,
            'total' => $amount,
            'date' => $duplicateDepositDate->toDateTimeString(),
        ]));

        $duplicateDeposit = Deposit::create([
            'deposit_type' => 'caregiver',
            'caregiver_id' => $caregiver->id,
            'amount' => $amount,
            'adjustment' => 1,
            'success' => 1,
            'created_at' => $duplicateDepositDate->toDateTimeString(),
        ]);
        $duplicateInvoice->addDeposit($duplicateDeposit, $amount);

        \DB::commit();

        $this->output->writeln("An invoice item for $amount was added to a new invoice {$duplicateInvoice->getName()} to record the original overpayment.");
    }
}
