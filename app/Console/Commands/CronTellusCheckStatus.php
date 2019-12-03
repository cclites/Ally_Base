<?php

namespace App\Console\Commands;

use App\Billing\ClaimStatus;
use App\TellusFile;
use App\TellusFileResult;
use App\Services\TellusService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use SimpleXMLElement;

class CronTellusCheckStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tellus:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all stored Tellus services for new response files and update the related claims.';

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
        $pendingFiles = TellusFile::where('status', TellusFile::STATUS_PENDING)->get();

        if ($pendingFiles->count() === 0) {
            $this->status('There are no pending files.');
            return;
        }

        /** @var \App\TellusFile $tellusFile */
        foreach ($pendingFiles as $tellusFile) {
            /** @var \App\Business $business */
            $business = $tellusFile->claim->invoice->client->business;
            $this->status('Business ID ' . $business->id . ' - Checking for file: ' . $tellusFile->filename . '...');

            try {
                $tellus = new TellusService(
                    $business->tellus_username,
                    $business->getTellusPassword(),
                    config('services.tellus.endpoint')
                );
            } catch (\Exception $ex) {
                $this->status('Could not connect to Tellus.');
                continue;
            }

            $result = $tellus->downloadResponse($tellusFile->filename);
            if (! $result) {
                // No response file found, so we can skip
                $this->status('No response file yet.');
                continue;
            }

            \DB::beginTransaction();
            try {

                if( $result === 'accepted' ){

                    $this->status( 'Claim accepted.' );
                    $tellusFile->update([ 'status' => TellusFile::STATUS_ACCEPTED ]);
                    $tellusFile->claim->update([ 'status' => ClaimStatus::ACCEPTED() ]);
                } else {

                    $this->status( 'Claim rejected.' );
                    $this->status( 'Parsing response file...' );

                    $tellusFile->update(['status' => TellusFile::STATUS_REJECTED]);
                    $tellusFile->claim->update(['status' => ClaimStatus::REJECTED()]);

                    $this->parseResponse( $result, $tellusFile );
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
     * @param TellusFile $tellusFile
     * @return bool
     */
    protected function parseResponse(string $response, TellusFile $tellusFile) : bool
    {
        $header = null;
        $hasFailure = false;

        $xml = new SimpleXMLElement( $tellusFile );
        dd( $xml );

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

            $result = TellusFileResult::create([
                'tellus_file_id' => $tellusFile->id,
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
