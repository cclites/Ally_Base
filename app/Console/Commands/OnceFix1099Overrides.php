<?php

namespace App\Console\Commands;

use App\Caregiver1099;
use App\Caregiver1099Payer;
use App\CaregiverYearlyEarnings;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Console\Command;

class OnceFix1099Overrides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:fix-1099-overrides {timestamp} {ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix an issue where all 1099s were set as ally override';

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
        //caregiver_1099_payer = 'ally' and client_id in (122,192,229,743,771,1469,1480,1551,1674,2058,2078,2152,2366,2715,2743,2900,3184,7346,7357,7359,7361,7375,7401,7406,7561,8171,8490,11045,11727,12122,12202,12308,12666,12691,13081,13113,13158,13472,13478,13507,13769,13800,14091,14152,14247,23897,24320,24521,25234,25257,25614,26918,28741,29164,29167,29291,29296,29564,29643,30262,30494,30672,30905,30951,31048,31916,31976,32571,32905,33904) and created_at >= '2020-01-24 19:44:56'
        $ids = explode(',', $this->argument('ids'));
        $records = Caregiver1099::where('created_at', '>=', $this->argument('timestamp'))
            ->whereIn('client_id', $ids)
            ->where('year', '2019')
            ->get();

        $count = $records->count();

        if (! $this->confirm("Found $count records that match this criteria.  Delete these 1099s and re-create them?")) {
            $this->info("Exiting");
            return 1;
        }

        \DB::beginTransaction();

        foreach ($records as $record) {
            $record->delete();

            /** @var \App\CaregiverYearlyEarnings $earnings */
            $earnings = CaregiverYearlyEarnings::with('client', 'caregiver')
                ->where('business_id', $record->business_id)
                ->where('client_id', $record->client_id)
                ->where('caregiver_id', $record->caregiver_id)
                ->where('year', $record->year)
                ->first();

            if (empty($earnings)) {
                $this->error("Could not find earnings data for this caregiver and client." . print_r($record, true));
                return 1;
            }

            if ($errors = $earnings->getMissing1099Errors()) {

                $this->error("Could not create 1099 because of missing data.  Please fix the following" . print_r($errors, true));
                return 1;
            }

            $newRecord = $earnings->make1099Record();
            $newRecord->save();
        }

        if (! $this->confirm("Process completed, commit these changes?")) {
            $this->info("Exiting");
            return 1;
        }

        \DB::commit();

        $this->info("Success");
    }
}
