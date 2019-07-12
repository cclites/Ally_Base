<?php

namespace App\Console\Commands;

use App\Billing\Actions\ProcessChainDeposits;
use App\Billing\Actions\ProcessChainPayments;
use App\Billing\Gateway\AchExportFile;
use App\Billing\Gateway\HeritiageACHService;
use App\Billing\Gateway\OfflineAchFileGateway;
use App\Billing\Payments\DepositMethodFactory;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\PaymentMethodFactory;
use App\Business;
use App\BusinessChain;
use App\Responses\CreatedResponse;
use App\Responses\Resources\PaymentLog;
use Illuminate\Console\Command;

class AchOfflineChargeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:offline_charge {chain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an offline ACH transaction export for a business chain\'s client payments.';

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
     * @throws \Exception
     */
    public function handle()
    {
        if (! $chain = BusinessChain::find($this->argument('chain'))) {
            $this->error("Chain ID not found.");
            return false;
        }

        $bank = $this->choice("Which bank would you like use to process ACH payments?", ["Heritage", "KeyBank"], 0);

        if (! $this->confirm("You are about to process all outstanding ACH and ACH-P client invoices for {$chain->name} using a {$bank} Bank export file.  Do you wish to continue?")) {
            $this->error('Operation canceled');
            return false;
        }

        $achFile = new AchExportFile(null, strtolower($bank));
        $achGateway = new OfflineAchFileGateway($achFile);
        $methodFactory = new PaymentMethodFactory($achGateway, null);
        $action = new ProcessChainPayments($methodFactory);

        \DB::beginTransaction();
        $results = $action->processPayments($chain, [Business::class, BankAccount::class]);
        $collection = PaymentLog::collection($results)->toArray(null);
        $filepath = $achFile->write();
        \DB::commit();
        $this->info("ACH export file written to $filepath.");

        $headers = ['log_id', 'batch_id', 'payment_id', 'payment_method', 'amount', 'success', 'exception', 'error_message', 'invoice ids'];
        $collection = collect($collection)->map(function ($item) {
            $item['invoices'] = collect($item['invoices'])->pluck('id')->implode(', ');
            return $item;
        });
        $this->info("Results:");
        $this->table($headers, $collection);

        return true;
    }
}
