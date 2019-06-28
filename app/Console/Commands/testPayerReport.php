<?php

namespace App\Console\Commands;

use App\Billing\Queries\ClientInvoiceQuery;
use Illuminate\Console\Command;
use App\Http\Controllers\Clients;
use App\Billing\ClientInvoice;
use App\Billing\ClientPayer;

use Log;
class testPayerReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payerReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $clientId = 13090;
        $clientPayerId = 8851;

        $invoiceQuery = new ClientInvoiceQuery;

        $invoices = $invoiceQuery->forClient($clientId, false)
            ->where('client_payer_id', $clientPayerId)
            ->get()
            ->map(function (ClientInvoice $item) {
                $item->billable = $item->getItems();
                return $item;
            });

        foreach($invoices as $invoice){
            Log::info("INVOICE");
            Log::info($invoice);
            Log::info("\n\n");

            //Log::info(gettype($invoice->billable));
            //Log::info($invoice->billable);

            foreach ($invoice->billable as $item){
                Log::info($item);
                Log::info("\n");
            }


        }
    }
}
