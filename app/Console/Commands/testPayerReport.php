<?php

namespace App\Console\Commands;

use App\Billing\Queries\ClientInvoiceQuery;
use Illuminate\Console\Command;
use App\Http\Controllers\Clients;
use App\Billing\ClientInvoice;
use App\Billing\ClientPayer;



use App\Reports\PayerInvoiceReport;

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


    protected $report;

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

        $start = '06/03/2019';
        $end = '06/30/2019';
        $confirmed = null;
        $charged = null;
        $payer = null;
        $company = 55;

        $params = [
          'start'=>$start,
          'end'=>$end,
          'confirmed'=>$confirmed,
          'charged'=>$charged,
          'company'=>$company,
          'payer'=>$payer,
          'json'=>1
        ];

        $url="http://krioscare.test/business/reports/payer-invoice-report?start=$start&end=$end&confirmed=$confirmed&payer=$payer&company=$company&json=1";
        //$url="reports.payer-invoice-report";
        echo $url;
        //return redirect()->route($url, $params);
        //echo file_get_contents($url);
        /*
        $businessId = 55;

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


        }*/
    }
}
