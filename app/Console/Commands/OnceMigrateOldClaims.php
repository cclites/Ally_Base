<?php

namespace App\Console\Commands;

use App\Billing\Claim;
use App\Billing\ClaimStatus;
use App\Billing\ClientInvoice;
use App\Business;
use App\Claims\ClaimInvoiceStatusHistory;
use App\Claims\Factories\ClaimInvoiceFactory;
use Illuminate\Console\Command;

class OnceMigrateOldClaims extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:migrate-claims {chain_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old claims into new system.';

    /**
     * @var ClaimInvoiceFactory
     */
    protected $claimFactory;

    protected $duplicates = 0;
    protected $success = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->claimFactory = new ClaimInvoiceFactory();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $businesses = collect();
        if ($chainId = $this->argument('chain_id')) {
            $businesses = Business::where('chain_id', $chainId)
                ->get();
        }

        if ($businesses->isNotEmpty()) {
            if (! $this->confirm("Migrate old claims for the following businesses?\n".$businesses->implode('name', ', '))) {
                return 0;
            }
        } else {
            if (! $this->confirm("Migrate old claims for every business in the system?")) {
                return 0;
            }
        }

        \DB::beginTransaction();

        $query = Claim::with(['statuses', 'invoice.client', 'invoice.claimInvoices', 'hhaFiles.results']);

        if ($businesses->isNotEmpty()) {
            $query->whereHas('invoice', function ($q) use ($businesses) {
                $q->whereHas('client', function ($q) use ($businesses) {
                    $q->forBusinesses($businesses->pluck('id')->toArray());
                });
            });
        }

        $count = $query->count();

        $this->info("Found $count invoices that need to be converted...");

        $query->chunk(400, function ($chunk) {
            $chunk->each(function ($oldClaim) {
                // Ignore invoices that have already been created using Claims 2.0
                if ($oldClaim->invoice->claimInvoices->count() > 0) {
                    $this->duplicates++;
                    return;
                }

                list($newClaim, $warnings) = $this->claimFactory->createFromClientInvoice($oldClaim->invoice);
                foreach ($warnings as $warning) {
                    $this->warn($warning);
                }

                if ($newClaim->amount != $oldClaim->amount) {
                    $this->warn("Claim amount mismatch - Old: $oldClaim->id = $oldClaim->amount - New: $newClaim->id $newClaim->amount");
                }

                // Create status history records
                $transmittedAt = null;
                foreach ($oldClaim->statuses->sortByDesc('created_at') as $oldStatus) {
                    if ($oldStatus->status == ClaimStatus::CREATED()) {
                        continue;
                    }

                    if (empty($transmittedAt) && $oldStatus->status == ClaimStatus::TRANSMITTED()) {
                        $transmittedAt = $oldStatus->created_at;
                    }

                    \DB::table('claim_invoice_status_history')->insert([[
                        'claim_invoice_id' => $newClaim->id,
                        'status' => $oldStatus->status,
                        'created_at' => $oldStatus->created_at,
                        'updated_at' => $oldStatus->updated_at,
                    ]]);
                }

                // Create HHA file records
                foreach ($oldClaim->hhaFiles as $oldFile) {
                    $newFileId = \DB::table('claim_invoice_hha_files')->insertGetId([
                        'claim_invoice_id' => $newClaim->id,
                        'filename' => $oldFile->filename,
                        'status' => $oldFile->status,
                        'created_at' => $oldFile->created_at,
                        'updated_at' => $oldFile->updated_at,
                    ]);

                    foreach ($oldFile->results as $oldResult) {
                        \DB::table('claim_invoice_hha_file_results')->insert([[
                            'hha_file_id' => $newFileId,
                            'service_date' => $oldResult->service_date,
                            'reference_id' => $oldResult->reference_id,
                            'service_code' => $oldResult->service_code,
                            'status_code' => $oldResult->status_code,
                            'import_status' => $oldResult->import_status,
                            'created_at' => $oldResult->created_at,
                            'updated_at' => $oldResult->updated_at,
                        ]]);
                    }
                }

                // Update other claim info
                $newClaim->transmission_method = $oldClaim->service;
                $newClaim->status = $oldClaim->status;
                if (filled($transmittedAt)) {
                    $newClaim->transmitted_at = $transmittedAt;
                }

                // turn off timestamps so you can set manually
                $newClaim->timestamps = false;
                $newClaim->created_at = $oldClaim->created_at;
                $newClaim->save();

                $this->success++;
            });
        });

        $this->info("$this->duplicates invoices were already converted to new claims and were skipped.");
        $this->info("$this->success new claims were created.");

        \DB::commit();

        $this->info("Operation complete.");

        return 0;
    }
}
