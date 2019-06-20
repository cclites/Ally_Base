<?php

namespace App\Console\Commands;

use App\BusinessChain;
use App\Reports\CaregiverContactInfoReport;
use Illuminate\Console\Command;

class DumpCaregiverContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:caregiver-contacts {chain_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump table of caregiver contacts.';

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
        $chain = BusinessChain::findOrFail($this->argument('chain_id'));

        $filter = $this->choice('What type of Caregiver\'s would you like see?', [
            'All',
            'Active',
            'Active and Not Set Up',
            'Active and Not Set Up but on the Schedule',
        ], 4);

        $report = new CaregiverContactInfoReport();
        $report->query()->forChains([$chain->id]);

        switch ($filter) {
            case 'All':
                break;
            case 'Active':
                $report->query()->active(true);
                break;
            case 'Active and Not Set Up':
                $report->query()->active()->whereNotSetup();
                break;
            case 'Active and Not Set Up but on the Schedule':
            default:
                $report->query()->active()->whereScheduled()->whereNotSetup();
                break;
        }

        $this->info($report->toCsv());
        $this->info('Found ' . $report->count() . ' Results');
    }
}
