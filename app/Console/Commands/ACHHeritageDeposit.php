<?php

namespace App\Console\Commands;

use App\Billing\Actions\ProcessChainDeposits;
use App\Billing\Gateway\AchExportFile;
use App\Billing\Gateway\HeritiageACHService;
use App\Billing\Payments\DepositMethodFactory;
use App\BusinessChain;
use Illuminate\Console\Command;

class ACHHeritageDeposit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:heritage_deposit {chain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'General a Heritage transaction export for a business chain\'s deposits';

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
        if (!$chain = BusinessChain::find($this->argument('chain'))) {
            $this->output->error("Chain ID not found.");
            return false;
        }

        $this->output->writeln("You are about to process outstanding deposit invoices for {$chain->name}.");

        if ($this->confirm('Do you wish to continue?')) {
            $achFile = new AchExportFile();
            $achProcessor = new HeritiageACHService($achFile);
            $methodFactory = new DepositMethodFactory($achProcessor);
            $processChainDeposits = new ProcessChainDeposits($methodFactory);

            \DB::beginTransaction();
            $processChainDeposits->processDeposits($chain);
            $filepath = $achFile->write();
            \DB::commit();

            $this->output->writeln("ACH export file written to $filepath.");
        }
    }
}
