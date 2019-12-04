<?php

namespace App\Console\Commands;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;
use App\Billing\DepositLog;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\Billing\PaymentLog;
use Illuminate\Console\Command;

class DeleteTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:delete {transaction_id : The Ally transaction IDs separate by comma}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a transaction record and optionally un-invoice and un-authorize related shifts.';

    /**
     * @var bool
     */
    protected $uninvoice = false;

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
     * @throws \Exception
     */
    public function handle()
    {
        $transactionId = $this->argument('transaction_id');
        if (strpos($transactionId, ',') > 0) {
            $transactionIds = explode(',', $transactionId);
        } else {
            $transactionIds = [$transactionId];
        }

        $transactions = GatewayTransaction::whereIn('id', $transactionIds)->get();
        if ($transactions->isEmpty()) {
            $this->error("No transactions found.  Be sure you are using the internal Ally Transaction IDs.");
            return 1;
        } else {
            $this->info("Found " . count($transactions) . ' transactions.');
        }

        if (!$this->confirm("Do you want to delete these transaction and their related payment/deposit records from the database?")) {
            return 0;
        }

        if ($this->confirm("Do you want to also un-invoice any related invoices?")) {
            $this->uninvoice = true;
        }

        \DB::beginTransaction();;
        foreach ($transactions as $transaction) {
            if ($payment = $transaction->payment) {
                if (!$this->deletePaymentTransaction($transaction, $payment)) {
                    // error -> do not commit
                    return 1;
                }
            } else if ($deposit = $transaction->deposit) {
                if (!$this->deleteDepositTransaction($transaction, $deposit)) {
                    // error -> do not commit
                    return 1;
                }
            }
        }
        \DB::commit();

        $this->info("Operation successful!");
    }

    /**
     * Delete a deposit transaction.
     *
     * @param GatewayTransaction $transaction
     * @param Deposit $deposit
     * @return bool
     * @throws \Exception
     */
    public function deleteDepositTransaction(GatewayTransaction $transaction, Deposit $deposit): bool
    {
        switch ($deposit->deposit_type) {
            case 'caregiver':
                $invoices = $deposit->caregiverInvoices;
                $type = 'Caregiver';
                break;
            case 'business':
                $invoices = $deposit->businessInvoices;
                $type = 'Business';
                break;
            default:
                $this->error("Unknown deposit type $deposit->deposit_type");
                return false;
        }
        $invoiceIds = $invoices->pluck('id')->toArray();

        if ($transaction->failedTransaction) {
            $transaction->failedTransaction->delete();
        }

        foreach ($invoices as $invoice) {
            /** @var \App\Billing\BusinessInvoice $invoice */
            $invoice->removeDeposit($deposit);
            $invoice->deposits()->detach($deposit->id);
        }
        $transaction->history()->delete();
        \DB::table('invoice_deposits')->where('deposit_id', $deposit->id)->delete();
        $transaction->delete();
        DepositLog::where('deposit_id', $deposit->id)->delete();
        $deposit->delete();

        $this->info("Transaction #{$transaction->id} and it's related deposit #{$deposit->id} have been removed.");

        if ($this->uninvoice) {
            foreach ($invoices as $invoice) {
                if (!$invoice->delete()) {
                    $this->error("Error attempting uninvoice of #{$invoice->id}.  Escaping sequence (no data written)");
                    return false;
                }
            }

            $this->info("The following related $type Invoices have been un-invoiced: " . join(', ', $invoiceIds));
        }

        return true;
    }

    /**
     * Delete a Payment Transaction.
     *
     * @param GatewayTransaction $transaction
     * @param Payment $payment
     * @return bool
     * @throws \Exception
     */
    public function deletePaymentTransaction(GatewayTransaction $transaction, Payment $payment): bool
    {
        $invoices = $payment->invoices;
        $invoiceIds = $invoices->pluck('id')->toArray();

        if ($transaction->failedTransaction) {
            $transaction->failedTransaction->delete();
        }

        // Un-apply all the the payment from all invoices and fix the balance.
        // Source: UnapplyFailedPayments.php
        foreach ($payment->invoices as $invoice) {
            /** @var \App\Billing\ClientInvoice $invoice */
            $invoice->removePayment($payment);
            $invoice->payments()->detach($payment->id);
        }
        $transaction->history()->delete();
        \DB::table('invoice_payments')->where('payment_id', $payment->id)->delete();
        $transaction->delete();
        PaymentLog::where('payment_id', $payment->id)->delete();
        $payment->delete();

        $relatedShiftIds = $invoices->map(function ($invoice) {
            /** @var ClientInvoice $invoice */
            return $invoice->items->map(function ($item) {
                /** @var ClientInvoiceItem $item */
                if ($shift = $item->getInvoiceable()->getShift()) {
                    return $shift->id;
                }
                return null;
            })
                ->filter();
        })
            ->flatten(1)
            ->unique()
            ->toArray();

        $this->info("Transaction #{$transaction->id} and it's related payment #{$payment->id} have been removed.");

        if ($this->uninvoice) {
            foreach ($invoices as $invoice) {
                if (!$invoice->delete()) {
                    $this->error("Error attempting uninvoice of #{$invoice->id}.  Escaping sequence (no data written)");
                    return false;
                }
            }
            $this->info("The following related Client Invoices have been un-invoiced: " . join(', ', $invoiceIds));
        }

        $this->info("Found " . count($relatedShiftIds) . " related shifts with the following IDs: " . join(', ', $relatedShiftIds));

        return true;
    }
}
