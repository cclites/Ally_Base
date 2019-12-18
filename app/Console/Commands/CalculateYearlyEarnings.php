<?php

namespace App\Console\Commands;

use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\Traits\Console\HasProgressBars;
use App\CaregiverYearlyEarnings;
use Illuminate\Console\Command;
use App\Traits\Console\HasLog;
use Carbon\Carbon;
use App\Client;

class CalculateYearlyEarnings extends Command
{
    use HasProgressBars, HasLog;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:yearly-earnings {year}';

    /**
     * @var OnlineClientInvoiceQuery
     */
    protected $query;

    /**
     * @var string|int
     */
    protected $year;

    /**
     * @var array
     */
    protected $log = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate the earnings for all caregivers per client for the calendar year given.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->query = new OnlineClientInvoiceQuery();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        if (!$this->year = $this->getYear()) {
            $this->error('Invalid year: ' . $year);
            return 0;
        }

        // This command can only be run once per year.  All data for the
        // year should be purged before the command can execute.
        if (CaregiverYearlyEarnings::where('year', $this->year)->exists()) {
            if (!$this->confirm("There is already earnings data for the year $this->year, are you sure you want to continue?  This will remove all previously calculated earnings data.")) {
                $this->info('Operation cancelled.');
                return 0;
            }

            $this->info("Removing old data for $this->year...");
            CaregiverYearlyEarnings::where('year', $this->year)->delete();
        }

        \DB::beginTransaction();

        $this->query->forDateRange([Carbon::parse("{$this->year}-01-01 00:00:01"), Carbon::parse("{$this->year}-12-31 23:59:59")])
            ->paidInFull();

        $clients = $this->getClients();

        $this->info('Found ' . number_format($clients->count()) . " clients with shifts for year $this->year");
        $this->startProgress("Calculating earnings for each client/caregiver", $clients->count());

        foreach ($clients as $client) {
            $summary = $this->getClientSummary($client->id);

            if (count($summary) === 0) {
                // No data for this client
                continue;
            }

            $records = [];
            foreach ($summary as $caregiverId => $earnings) {
                $records[] = [
                    'year' => $this->year,
                    'client_id' => $client->id,
                    'caregiver_id' => $caregiverId,
                    'business_id' => $client->business_id,
                    'earnings' => $earnings,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            CaregiverYearlyEarnings::insert($records);

            $this->advance();
        }

        \DB::commit();

        $this->finish();

        if (count($this->getLog())) {
            $this->warn("The following issues occurred while trying to calculate the cost of shifts and services:\n" . implode("\n", $this->getLog()));
        }

        $this->info("Operation complete.");
        return 0;
    }

    /**
     * Get the current year from the command line argument.
     *
     * @return int
     */
    public function getYear(): ?int
    {
        $year = $this->argument('year');

        if ($year < 2015 || $year > Carbon::now()->format('Y') || strlen($year) != 4 || !is_numeric($year)) {
            return null;
        }

        return (int)$year;
    }

    /**
     * Get clients who have invoices for the given year.
     *
     * @return iterable
     */
    public function getClients(): iterable
    {
        $clientIds = (clone $this->query)
            ->selectRaw('DISTINCT(client_id) as client_id')
            ->pluck('client_id');

        return Client::whereIn('id', $clientIds)->get();
    }

    /**
     * Get a breakdown of caregiver earnings for the client.
     *
     * @param int $clientId
     * @return array
     */
    public function getClientSummary(int $clientId): array
    {
        $hashTable = [];
        $shifts = [];

        (clone $this->query)
            ->where('client_id', $clientId)
            ->with([
                'items',
                'items.shift.costHistory',
                'items.shift.client',
                'items.shift.client.user',
                'items.shift.services',
                'items.shiftService',
                'items.shiftService.shift.client.user',
                'items.shiftService.shift.costHistory',
            ])
            ->chunk(500, function ($chunk) use ($clientId, &$hashTable, &$shifts) {
                foreach ($chunk as $invoice) {
                    foreach ($invoice->items->whereIn('invoiceable_type', ['shifts', 'shift_services']) as $item) {
                        /** @var \App\Shift $shift */
                        $shift = null;
                        if ($item->shift != null) {
                            $shift = $item->shift;
                        } else if ($item->shiftService != null) {
                            $shift = $item->shiftService->shift;

                            if (empty($shift)) {
                                // related shift gone missing
                                $this->log("Could not find related shift {$item->shiftService->shift_id} for invoiceable {$item->invoiceable_type} {$item->invoiceable_id}", null);
                                continue;
                            }

                        } else {
                            // invoiceable gone missing
                            $this->log("Could not find invoiceable {$item->invoiceable_type} {$item->invoiceable_id}", null);
                            continue;
                        }

                        if (in_array($shift->id, $shifts)) {
                            // Already counted this shift and all of its services.
                            continue;
                        }

                        if (empty($shift->caregiver_id)) {
                            $this->log("Shift has no caregiver assigned: #{$shift->id}", null);
                            $shifts[] = $shift->id;
                            continue;
                        }

                        $caregiverTotal = $shift->costs()->getCaregiverCost(false);

                        if (!isset($hashTable[$shift->caregiver_id])) {
                            $hashTable[$shift->caregiver_id] = $caregiverTotal;
                        } else {
                            $hashTable[$shift->caregiver_id] = add($hashTable[$shift->caregiver_id], $caregiverTotal);
                        }

                        $shifts[] = $shift->id;
                    }
                }
            });

        return $hashTable;
    }
}
