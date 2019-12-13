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

            list( $result, $status ) = $tellus->downloadResponse( $tellusFile->filename );

            if (! $result) {
                // No response file found, so we can skip
                $this->status('No response file yet.');
                continue;
            }

            \DB::beginTransaction();
            try {

                $this->status( "Claim $status." );
                $tellusFile->update([ 'status' => $status ]);

                if( $status === TellusFile::STATUS_ACCEPTED ){

                    $tellusFile->claim->update([ 'status' => ClaimStatus::ACCEPTED() ]);
                } else {

                    $tellusFile->claim->update(['status' => ClaimStatus::REJECTED()]);
                }

                $this->status( 'Parsing response file...' );
                $this->parseResponse( $result, $tellusFile, $status );

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
     * @return void
     */
    protected function parseResponse(string $response, TellusFile $tellusFile, string $status) : void
    {
        $hasFailure = false;

        $xml = new SimpleXMLElement( $response );

        foreach( $xml->RenderedService as $service ) {

            if( count( $service->Errors ) === 0 ){
                // success only has 1 field

                $result = TellusFileResult::create([

                    'tellus_file_id' => $tellusFile->id,
                    'reference_id'   => $service->VisitId ?? null, // the shift id, it has an optional() wrapping
                    'service_code'   => $service->ServiceCode,
                    'status_code'    => 200,
                    'import_status'  => $status,
                    'service_date'   => Carbon::parse( $service->ActualStartDateTime ),
                ]);
            } else {
                // errors have multiple fields associated to one rendered service

                foreach( $service->Errors->Error as $error ){

                    $result = TellusFileResult::create([

                        'tellus_file_id' => $tellusFile->id,
                        'reference_id'   => $service->VisitId ?? null, // the shift id, it has an optional() wrapping
                        'service_code'   => $service->ServiceCode,
                        'status_code'    => $error->ErrorCode,
                        'import_status'  => $error->ErrorDescription,
                        'service_date'   => Carbon::parse( $service->ActualStartDateTime ),
                    ]);
                }
            }
        }
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
