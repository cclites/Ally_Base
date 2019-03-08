<?php

namespace App\Console\Commands;

use App\Billing\Actions\ApplyPayment;
use App\Billing\ClientInvoice;
use App\Billing\Payment;
use App\Billing\Payments\Methods\CreditCard;
use Illuminate\Console\Command;

class InvoiceManualPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:manual_payment {client_invoice_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary command to apply a manual payment to invoices.';

    /**
     * @var \App\Billing\Actions\ApplyPayment
     */
    protected $paymentApplicator;

    /**
     * Create a new command instance.
     *
     * @param \App\Billing\Actions\ApplyPayment $paymentApplicator
     */
    public function __construct(ApplyPayment $paymentApplicator)
    {
        parent::__construct();
        $this->paymentApplicator = $paymentApplicator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$invoice = ClientInvoice::find($this->argument('client_invoice_id'))) {
            $this->output->error("The invoice ID " . $this->argument('client_invoice_id') . " does not exist.");
            return 1;
        }

        if ($invoice->amount_paid) {
            $this->output->error("This invoice already has an amount paid.");
            return 2;
        }

        $this->output->writeln("You are about to create an manual payment record of {$invoice->amount} for client {$invoice->client->name}.");

        if ($this->confirm('Do you wish to continue?')) {
            $payment = Payment::create([
                'client_id' => $invoice->client->id,
                'payment_type' => 'MANUAL',
                'amount' => $invoice->amount,
                'success' => true,
                'system_allotment' => $invoice->client->getAllyFee($invoice->amount),
            ]);
            if (!$payment) {
                $this->output->error("Payment could not be recorded.");
                return 3;
            }
            $this->paymentApplicator->apply($invoice, $payment);
            $this->output->writeln("Payment recorded!");
        }
    }
}
