<?php

namespace App\Console\Commands\Cron;

use App\ClaimInvoiceTellusFileResult;
use App\Services\TellusSftpException;
use Illuminate\Console\Command;
use App\Services\TellusService;
use App\ClaimInvoiceTellusFile;
use App\Claims\ClaimStatus;
use SimpleXMLElement;
use Carbon\Carbon;

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
    protected $description = 'Check all stored Tellus services for new response files and update the related claims (2.0).';

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
        $pendingFiles = ClaimInvoiceTellusFile::where('status', ClaimInvoiceTellusFile::STATUS_PENDING)->get();

        if ($pendingFiles->count() === 0) {
            $this->status('There are no pending Tellus files.');
            return;
        }

        /** @var ClaimInvoiceTellusFile $tellusFile */
        foreach ($pendingFiles as $tellusFile) {
            /** @var \App\Business $business */
            $business = $tellusFile->claimInvoice->business;
            $this->status('Business ID ' . $business->id . ' - Checking for file: ' . $tellusFile->filename . '...');

            $tellus = new TellusService(
                $business->tellus_username,
                $business->getTellusPassword(),
                config('services.tellus.endpoint')
            );

            try {
                list($contents, $status) = $tellus->getFileResult($tellusFile->filename);
            } catch (TellusSftpException $ex) {
                $this->status($ex->getMessage());
                continue;
            }

            if (empty($contents) || empty($status)) {
                // No response file found, so we can skip
                $this->status('No response file yet.');
                continue;
            }

            \DB::beginTransaction();
            try {
                $this->status("Claim $status.");
                $tellusFile->update(['status' => $status]);

                if ($status == ClaimInvoiceTellusFile::STATUS_ACCEPTED) {
                    $tellusFile->claimInvoice->update(['status' => ClaimStatus::ACCEPTED()]);
                } else {
                    $tellusFile->claimInvoice->update(['status' => ClaimStatus::REJECTED()]);
                }

                $this->status('Parsing response file...');
                $this->parseResponse($contents, $tellusFile, $status);

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
     * @param ClaimInvoiceTellusFile $tellusFile
     * @param string $status
     * @return void
     */
    protected function parseResponse(string $response, ClaimInvoiceTellusFile $tellusFile, string $status): void
    {
        $xml = new SimpleXMLElement($response);

        foreach ($xml->RenderedService as $service) {
            if (count($service->Errors) === 0) {
                // success only has 1 field

                ClaimInvoiceTellusFileResult::create([
                    'tellus_file_id' => $tellusFile->id,
                    'reference_id' => trim($service->UserField2) ?? null, // references ClaimInvoiceItem ID
                    'service_code' => trim($service->ServiceCode),
                    'status_code' => 200,
                    'import_status' => 'Success',
                    'service_date' => Carbon::parse(trim($service->UserField3)),
                ]);
            } else {
                // errors have multiple fields associated to one rendered service

                ClaimInvoiceTellusFileResult::create([
                    'tellus_file_id' => $tellusFile->id,
                    'reference_id' => trim($service->UserField2) ?? null, // references ClaimInvoiceItem ID
                    'service_code' => trim($service->ServiceCode),
                    'status_code' => 500,
                    'import_status' => $this->compileErrors($service->Errors),
                    'service_date' => Carbon::parse(trim($service->UserField3)),
                ]);
            }
        }
    }

    /**
     * Compile array of errors into readable string
     *
     * @param SimpleXMLElement $errors
     * @return string
     */
    public function compileErrors(SimpleXMLElement $errors): string
    {
        $data = collect([]);

        foreach ($errors->Error as $error) {
            $data->push(trim($error->ErrorCode) . ": " . trim($error->ErrorDescription));
        }

        return implode(", ", $data->toArray());
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
