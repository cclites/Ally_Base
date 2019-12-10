<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RetroactiveDecommisionLetters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:decommision_letters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Go back and retroactively create decommision letters for all users who did not get one from before the feature was created';

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
