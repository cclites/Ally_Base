<?php

namespace App\Console\Commands;

use App\Billing\ClientInvoice;
use App\Claims\Factories\ClaimInvoiceFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateClaimsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claims:generate
                            {client_invoice_ids : Comma separated list of client invoice ids.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Claim for the given client invoice ids.';

    /** @var ClaimInvoiceFactory */
    private $generator;

    /** @var Collection */
    protected $dupes;

    /** @var Collection */
    protected $errors;

    /** @var Collection */
    protected $warnings;

    /** @var Collection */
    protected $claims;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->generator = app(ClaimInvoiceFactory::class);
        $this->dupes = collect([]);
        $this->errors = collect([]);
        $this->warnings = collect([]);
        $this->claims = collect([]);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ids = explode(',', $this->argument('client_invoice_ids'));

        $invoices = ClientInvoice::whereIn('id', $ids)->get();

        if ($invoices->isEmpty()) {
            $this->error('No client invoices found.');
            return 1;
        }

        if (! $this->confirm("Create claims from the following client invoices?\r\n".$invoices->pluck('id'))) {
            $this->info("Operation canceled.");
            return 0;
        }

        try {
            \DB::beginTransaction();

            $this->info("Creating {$invoices->count()} claims...");

            foreach ($invoices as $invoice) {
                /** @var ClientInvoice $invoice */
                if (filled($invoice->claimInvoice)) {
                    $this->dupes->push($invoice->id);
                    continue;
                }

                try {
                    /** @var Collection $warnings */
                    list($claim, $warnings) = $this->generator->createFromClientInvoice($invoice);
                    $this->claims->push($claim);
                    $warnings->each(function ($item) use ($invoice) {
                        $this->warnings->push("Invoice {$invoice->id} (#{$invoice->name}): {$item}");
                    });
                } catch (\Exception $ex) {
                    $this->errors->push("Invoice {$invoice->id} (#{$invoice->name}): {$ex->getMessage()}");
                    continue;
                }
            }

            \DB::commit();
        } catch (\Exception $ex) {
            $this->error("Unexpected error: {$ex->getMessage()}");
            \DB::rollBack();
            return 1;
        }

        $this->info("Created {$this->claims->count()} claims.");
        if ($this->dupes->count()) {
            $this->info("{$this->dupes->count()} Invoices already have claims created: $this->dupes");
        }

        if ($this->errors->count()) {
            $this->info("{$this->errors->count()} Invoices could not be processed:");
            foreach ($this->errors as $item) {
                $this->info($item);
            }
        }

        if ($this->warnings->count()) {
            $this->info("{$this->warnings->count()} Warnings were triggered:");
            foreach ($this->warnings as $item) {
                $this->info($item);
            }
        }

        return 0;
    }
}
