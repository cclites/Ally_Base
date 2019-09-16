<?php

namespace App\Console\Commands;

use App\Billing\Claim;
use App\Billing\ClaimService;
use App\Billing\ClaimStatus;
use App\Billing\ClientInvoice;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:stuff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test stuff.';

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
        if (config('app.env') == 'production') {
            return;
        }
<<<<<<< HEAD

        \DB::beginTransaction();
        $invoice = ClientInvoice::find(30269);
        $claim = Claim::getOrCreate($invoice);
        $transmitter = Claim::getTransmitter(ClaimService::TELLUS());

        if ($transmitter->isTestMode($claim)) {
            $testFile = $transmitter->test($claim);
        } else {
            $transmitter->send($claim);
            $claim->updateStatus(ClaimStatus::TRANSMITTED(), [
                'service' => $service,
            ]);
        }

//        \DB::commit();
=======
>>>>>>> origin/master
    }
}
