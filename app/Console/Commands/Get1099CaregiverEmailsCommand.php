<?php

namespace App\Console\Commands;

use App\Caregiver;
use App\Caregiver1099;
use App\CaregiverYearlyEarnings;
use Illuminate\Console\Command;

class Get1099CaregiverEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1099:caregiver-emails {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get contact information for all caregivers who will receive 1099s in the given year.';

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
        $year = $this->argument('year');

        \Storage::disk('local')->makeDirectory('exports');
        $filename = \Storage::disk('local')->path("exports/caregivers-receiving-1099-$year.csv");
        $this->info("Fetching Caregivers who are set to receive 1099s in $year...");

        $data = CaregiverYearlyEarnings::with(['client', 'caregiver', 'business'])
            ->overThreshold(Caregiver1099::THRESHOLD)
            ->whereHas('caregiver')
            ->whereHas('client', function ($q) {
                $q->where('send_1099', 'yes');
            })
            ->where('year', $year)
            ->get()
            ->unique('caregiver_id')
            ->map(function (CaregiverYearlyEarnings $item) {
                return [
                    'id' => optional($item->caregiver)->id,
                    'caregiver' => $item->caregiver->name,
                    'email' => $item->caregiver->email,
                    'business' => $item->business->name,
                ];
            })
            ->sortBy('caregiver')
            ->values();

        $count = $data->count();

        $this->info("Saving $count results to: $filename...");

        if (! dump_csv($filename, $data)) {
            $this->error('Error saving file!');
            return 1;
        }

        $this->info("Success!");

        return 0;
    }
}
