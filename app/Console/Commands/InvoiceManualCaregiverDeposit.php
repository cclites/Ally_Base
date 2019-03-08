<?php

namespace App\Console\Commands;

use App\Billing\Actions\ApplyDeposit;
use App\Billing\CaregiverInvoice;
use App\Billing\Deposit;
use Illuminate\Console\Command;

class InvoiceManualCaregiverDeposit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:manual_caregiver_deposit {caregiver_invoice_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary command to apply a manual deposit to invoices.';

    /**
     * @var \App\Billing\Actions\ApplyDeposit
     */
    protected $depositApplicator;

    /**
     * Create a new command instance.
     *
     * @param \App\Billing\Actions\ApplyDeposit $depositApplicator
     */
    public function __construct(ApplyDeposit $depositApplicator)
    {
        parent::__construct();
        $this->depositApplicator = $depositApplicator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$invoice = CaregiverInvoice::find($this->argument('caregiver_invoice_id'))) {
            $this->output->error("The invoice ID " . $this->argument('caregiver_invoice_id') . " does not exist.");
            return 1;
        }

        if ($invoice->amount_paid) {
            $this->output->error("This invoice already has an amount paid.");
            return 2;
        }

        $this->output->writeln("You are about to create an manual deposit record of {$invoice->amount} for caregiver {$invoice->caregiver->name}.");

        if ($this->confirm('Do you wish to continue?')) {
            $deposit = Deposit::create([
                'caregiver_id' => $invoice->caregiver->id,
                'amount' => $invoice->amount,
                'success' => true,
                'notes' => 'Manual Deposit'
            ]);
            if (!$deposit) {
                $this->output->error("Deposit could not be recorded.");
                return 3;
            }
            $this->depositApplicator->apply($invoice, $deposit);
            $this->output->writeln("Deposit recorded!");
        }
    }
}
