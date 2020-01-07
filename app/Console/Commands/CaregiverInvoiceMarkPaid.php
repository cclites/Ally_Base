<?php

namespace App\Console\Commands;

use App\Billing\Actions\ApplyDeposit;
use App\Billing\CaregiverInvoice;
use App\Billing\Deposit;
use Illuminate\Console\Command;

class CaregiverInvoiceMarkPaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'caregiver-invoice:mark-paid {invoice_id} {notes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark given Caregiver invoice as paid without actual transaction.';

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
     * @param ApplyDeposit $depositApplicator
     * @return mixed
     * @throws \App\Billing\Exceptions\PaymentAmountError
     */
    public function handle(ApplyDeposit $depositApplicator)
    {
        /** @var CaregiverInvoice $invoice */
        $invoice = CaregiverInvoice::findOrFail($this->argument("invoice_id"));

        if (! $this->confirm("Mark this invoice as paid?\r\n#{$invoice->id} - {$invoice->caregiver->name} - \${$invoice->amount}")) {
            return;
        }

        $deposit = Deposit::create([
            'deposit_type' => 'caregiver',
            'caregiver_id' => $invoice->caregiver_id,
            'business_id' => null,
            'amount' => $invoice->getAmountDue(),
            'transaction_id' => null,
            'transaction_code' => null,
            'adjustment' => 1,
            'notes' => $this->argument('notes'),
            'success' => 1,
        ]);

        $depositApplicator->apply($invoice, $deposit, $invoice->getAmountDue());

        $this->info("Successfully applied deposit #{$deposit->id} to invoice #{$invoice->id}");
    }
}
