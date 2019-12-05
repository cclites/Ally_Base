<?php

namespace App\Console\Commands;

use App\Billing\ClaimStatus;
use App\HhaFile;
use App\HhaFileResult;
use App\Services\HhaExchangeService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CronHhaCheckStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hha:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all stored HHA services for new response files and update the related claims.';

    /**
     * Keep a log of the CRON process.
     *
     * @var \Illuminate\Support\Collection|null
     */
    protected $log = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->log = collect([]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $pendingFiles = HhaFile::where('status', HhaFile::STATUS_PENDING)->get();

        if ($pendingFiles->count() === 0) {
            $this->status('There are no pending files.');
            return;
        }

        /** @var \App\HhaFile $hhaFile */
        foreach ($pendingFiles as $hhaFile) {
            /** @var \App\Business $business */
            $business = $hhaFile->claim->invoice->client->business;
            $this->status('Business ID ' . $business->id . ' - Checking for file: ' . $hhaFile->filename . '...');

            try {
                $hha = new HhaExchangeService(
                    $business->hha_username,
                    $business->getHhaPassword(),
                    $business->ein
                );
            } catch (\Exception $ex) {
                $this->status('Could not connect to HHA.');
                continue;
            }

            $result = $hha->downloadResponse($hhaFile->filename . '_Log.csv');
            if (! $result) {
                // No response file found, so we can skip
                $this->status('No response file yet.');
                continue;
            }

            \DB::beginTransaction();
            try {
                $this->status('Parsing response file...');
                if ($this->parseResponse($result, $hhaFile)) {
                    $this->status('Claim accepted.');
                    $hhaFile->update(['status' => HhaFile::STATUS_ACCEPTED]);
                    $hhaFile->claim->update(['status' => ClaimStatus::ACCEPTED()]);
                 } else {
                    $this->status('Claim rejected.');
                    $hhaFile->update(['status' => HhaFile::STATUS_REJECTED]);
                    $hhaFile->claim->update(['status' => ClaimStatus::REJECTED()]);
                }

                \DB::commit();

            } catch (\InvalidArgumentException $ex) {
                app('sentry')->captureException($ex);
                $this->status($ex->getMessage());
                \DB::rollBack();
            }
        }
    }

    /**
     * Parse response file csv result and return a bool
     * whether the claim was accepted or not.
     *
     * @param string $response
     * @param HhaFile $hhaFile
     * @return bool
     */
    protected function parseResponse(string $response, HhaFile $hhaFile) : bool
    {
        $header = null;
        $hasFailure = false;
        foreach (explode("\r\n", $response) as $line) {
            if (empty($line)) {
                continue;
            }

            $csv = str_getcsv($line);
            if (! $header) {
                $header = $csv;
                continue;
            }

            if (count($csv) != 45) {
                throw new \InvalidArgumentException("Invalid response file line: $line");
            }

            $result = HhaFileResult::create([
                'hha_file_id' => $hhaFile->id,
                'reference_id' => $csv[9],
                'service_code' => $csv[10],
                'status_code' => $csv[43],
                'import_status' => $csv[44],
                'service_date' => Carbon::parse($csv[11]),
            ]);

            if ($result->status_code != '200') {
                $hasFailure = true;
            }
        }

        return !$hasFailure;
    }

    /**
     * Log and output the status.
     *
     * @param string $message
     */
    public function status(string $message)
    {
        $this->info($message);
        $this->log->push($message);
    }
}
