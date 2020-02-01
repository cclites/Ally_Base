<?php

namespace App\Console\Commands;

use App\Billing\ClientInvoice;
use App\Billing\Payments\PaymentMethodType;
use Illuminate\Console\Command;

class ConvertClientInvoiceToOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:invoice-to-offline {invoice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert a client invoice to an offline invoice.';

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
        $invoice = ClientInvoice::find($this->argument('invoice'));

        $invoiceTitle = "Invoice #{$invoice->name} (ID: $invoice->id Client: {$invoice->client->name})";

        if ($invoice->isOffline()) {
            $this->error("$invoiceTitle is already an OFFLINE invoice.");
            return 0;
        }

        if (! $invoice->clientPayer->payer->isOffline()) {
            $this->error("Payer {$invoice->clientPayer->payer->name()} is not OFFLINE, Cannot convert $invoiceTitle.");
            return 0;
        }

        if ($invoice->payments->count() > 0 || $invoice->amount_paid > 0.00) {
            $this->error("$invoiceTitle has online payments and cannot be converted to OFFLINE.");
            return 0;
        }

        if (! $this->confirm("Convert $invoiceTitle to OFFLINE?")) {
            return 0;
        }

        $invoice->update([
            'offline' => true,
        ]);

        return 0;
    }
}
