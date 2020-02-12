<?php

namespace App\Console\Commands;

use App\BusinessChain;
use App\Caregiver;
use Illuminate\Console\Command;

class SwapUserNamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:swap-names {chain_id} {--caregivers} {--clients} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swap user first names with their last names.';

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
        $chain = BusinessChain::find($this->argument('chain_id'));

        if (empty($chain)) {
            $this->error("Could not find a chain with that ID.");
            return 1;
        }

        $dryRun = $this->option('dry-run');
        $caregivers = $this->option('caregivers');
        $clients = $this->option('clients');

        if (! $caregivers && ! $clients) {
            $this->error("No user option select.  Please choose clients and/or caregivers.");
            return 1;
        }

        \DB::beginTransaction();

        $count = 0;
        if ($caregivers) {
            foreach ($chain->caregivers()->with('user')->get() as $caregiver) {
                $count++;
                $first = $caregiver->first_name;
                $last = $caregiver->last_name;
                $caregiver->user->update(['firstname' => $last, 'lastname' => $first]);

                if ($dryRun) {
                    $this->info("Caregiver $first $last = $last $first");
                }
            }
            $this->info("Updated $count caregiver records.");
        }

        $count = 0;
        if ($clients) {
            foreach ($chain->businesses as $business) {
                foreach ($business->clients()->with('user')->get() as $client) {
                    $first = $client->first_name;
                    $last = $client->last_name;
                    $client->user->update(['firstname' => $last, 'lastname' => $first]);

                    if ($dryRun) {
                        $this->info("Client $first $last = $last $first");
                    }
                }
            }
            $this->info("Updated $count client records.");
        }

        if (! $dryRun) {
            \DB::commit();
        }

        $this->info("Success.");

        return 0;
    }
}
