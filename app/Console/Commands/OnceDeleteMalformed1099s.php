<?php

namespace App\Console\Commands;

use App\Caregiver;
use App\Caregiver1099;
use App\Caregiver1099Payer;
use Illuminate\Console\Command;

class OnceDeleteMalformed1099s extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:delete-malformed-1099 {--dry-run : Do not delete anything just print the IDs.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete any created Caregiver 1099s that are malformed / have bad SSNs.';

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
        $badIds = Caregiver1099::all()
            ->map(function (Caregiver1099 $item) {
                if ($item->caregiver_1099_payer == Caregiver1099Payer::CLIENT() && ! valid_ssn(decrypt($item->client_ssn))) {
                    return $item->id;
                }
                if (! valid_ssn(decrypt($item->caregiver_ssn))) {
                    return $item->id;
                }

                return null;
            })
            ->filter();

        if ($badIds->isEmpty()) {
            $this->info("No malformed caregiver 1099s found.");
            return;
        }

        if ($this->option('dry-run')) {
            $this->info("Bad 1099 IDs: " . $badIds->implode(', '));
            return;
        }

        $this->info('Deleting ' . $badIds->count() . ' 1099s...');

        Caregiver1099::whereIn('id', $badIds)->delete();

        $this->info('Success');
    }
}
