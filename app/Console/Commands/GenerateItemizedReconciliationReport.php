<?php

namespace App\Console\Commands;

use App\Billing\BusinessInvoiceItem;
use Illuminate\Support\Collection;
use App\Billing\BusinessInvoice;
use Illuminate\Console\Command;
use App\Billing\Deposit;
use App\Billing\Payment;
use App\BusinessChain;
use Carbon\Carbon;

class GenerateItemizedReconciliationReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:itemized-reconciliation {chain_id} {start_date} {end_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate itemized reconciliation report for payments and deposits for the given chain and date range.';

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
        $chain = BusinessChain::find($this->argument('chain_id'));
        $businesses = $chain->businesses->pluck('id')->toArray();

        $start = Carbon::parse($this->argument('start_date'), 'America/New_York');
        $end = Carbon::parse($this->argument('end_date'), 'America/New_York');
        $startText = $start->format('Y-m-d');
        $endText = $end->format('Y-m-d');

        if (! $this->confirm("Generate itemized reconciliation report for payments and deposits between $startText and $endText for {$chain->name}?\nThis will take a while...", true)) {
            $this->info('Canceled.');
            return 0;
        }

        $this->info("Generating payments report for {$chain->name}...");
        $filename = \Storage::path("Itemized-Payments-{$chain->id}-$startText-$endText.csv");
        $payments = $this->paymentsReport($businesses, $start, $end);
        if ($payments->count() === 0) {
            $this->info("No payments found between $startText and $endText.");
        } else {
            $this->toCsv($filename, $payments);
            $this->info("Exported ".number_format($payments->count())." itemized payment records to $filename...");
        }

        $this->info("Generating deposits report for {$chain->name}...");
        $filename = \Storage::path("Itemized-Deposits-{$chain->id}-$startText-$endText.csv");
        $deposits = $this->depositsReport($businesses, $start, $end);
        if ($deposits->count() === 0) {
            $this->info("No deposits found between $startText and $endText.");
        } else {
            $this->toCsv($filename, $deposits);
            $this->info("Exported ".number_format($deposits->count())." itemized deposit records to $filename...");
        }

        $this->info('Complete.');
        return 0;
    }

    /**
     * Generate the itemized payments report.
     *
     * @param array $businesses
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function paymentsReport(array $businesses, Carbon $start, Carbon $end) : Collection
    {
        return Payment::where('payment_method_type', 'businesses')
            ->whereIn('business_id', $businesses)
            ->whereBetween('created_at', [$start->setTimezone('UTC'), $end->setTimezone('UTC')])
            ->with([
                'business',
                'invoices',
                'invoices.client',
                'invoices.client.user',
                'invoices.items',
                'invoices.items.invoiceable',
                'invoices.clientPayer',
            ])
            ->get()
            ->map(function (Payment $payment) {
                return $payment->invoices->map(function ($invoice) use ($payment) {
                    return $invoice->items->map(function ($item) use ($invoice, $payment) {
                        /** @var \App\Billing\Contracts\InvoiceableInterface $invoiceable */
                        $invoiceable = optional($item->getInvoiceable());
                        $caregiver = $invoiceable->getCaregiver();
                        $payer = optional($invoice->clientPayer)->payer;
                        return [
                            'office_location' => $payment->business->name,
                            'payment_id' => $payment->id,
                            'payment_amount' => $payment->amount,
                            'effect' => $payment->amount >= 0 ? 'withdrawal' : 'deposit',
                            'transaction_id' => $payment->transaction_id,
                            'transaction_date' => $payment->created_at->toDateString(),

                            'item_date' => optional($item->date)->toDateString() ?? $invoice->created_at->toDateString(),
                            'invoice_no' => $invoice->name,
                            'client_id' => $invoice->client->id,
                            'client_name' => $invoice->client->name,
                            'client_type' => $invoice->client->client_type,
                            'caregiver_id' => optional($caregiver)->id,
                            'caregiver_name' => optional($caregiver)->name,
                            'payer_id' => optional($payer)->id,
                            'payer_name' => optional($payer)->name,
                            'service_name' => $item->name,
                            'units' => $item->units,
                            'rate' => $item->rate,
                            'total' => $item->total,
                            'amount_due' => $item->amount_due,
                        ];
                    });
                });
            })->flatten(2);
    }

    /**
     * Generate the itemized deposits report.
     *
     * @param array $businesses
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function depositsReport(array $businesses, Carbon $start, Carbon $end) : Collection
    {
        return Deposit::whereIn('business_id', $businesses)
            ->whereNull('caregiver_id')
            ->whereBetween('created_at', [$start->setTimezone('UTC'), $end->setTimezone('UTC')])
            ->with([
                'businessInvoices',
                'businessInvoices.items',
                'businessInvoices.items.invoiceable',
                'business',
            ])
            ->get()
            ->map(function (Deposit $deposit) {
                return $deposit->businessInvoices->map(function (BusinessInvoice $invoice) use ($deposit) {
                    return $invoice->items->map(function (BusinessInvoiceItem $item) use ($invoice, $deposit) {
                        /** @var \App\Billing\Contracts\InvoiceableInterface $invoiceable */
                        $invoiceable = optional($item->getInvoiceable());
                        $client = $invoiceable->getClient();
                        $caregiver = $invoiceable->getCaregiver();
                        return [
                            'deposit_id' => $deposit->id,
                            'deposit_amount' => $deposit->amount,
                            'effect' => $deposit->amount >= 0 ? 'deposit' : 'withdrawal',
                            'transaction_id' => $deposit->transaction_id,
                            'transaction_date' => $deposit->created_at->toDateString(),
                            'office_location' => $deposit->business->name,

                            'item_date' => optional($item->date)->toDateString() ?? $invoice->created_at->toDateString(),
                            'invoice_no' => $invoice->name,
                            'client_id' => optional($client)->id,
                            'client_name' => optional($client)->name,
                            'client_type' => optional($client)->client_type,
                            'caregiver_id' => optional($caregiver)->id,
                            'caregiver_name' => optional($caregiver)->name,
                            'service_name' => $item->name,

                            'units' => $item->units,
                            'client_rate' => $item->client_rate,
                            'caregiver_rate' => $item->caregiver_rate,
                            'ally_rate' => $item->ally_rate,
                            'provider_rate' => $item->rate,
                            'client_total' => multiply($item->client_rate, $item->units, 4),
                            'caregiver_total' => multiply($item->caregiver_rate, $item->units, 4),
                            'ally_total' => multiply($item->ally_rate, $item->units, 4),
                            'provider_total' => $item->total,
                        ];
                    });
                });
            })->flatten(2);
    }

    /**
     * Create a CSV file with the report data.
     *
     * @param string $filename
     * @param Collection $data
     * @return void
     */
    public function toCsv(string $filename, Collection $data) : void
    {
        $output = fopen($filename, 'w');
        fputcsv($output, array_keys($data->first()));
        $data->each(function ($row) use ($output) {
            fputcsv($output, $row);
        });
        fclose($output);
    }
}
