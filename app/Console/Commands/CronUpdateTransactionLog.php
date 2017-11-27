<?php

namespace App\Console\Commands;

use App\Events\FailedTransaction;
use App\Gateway\ECSQuery;
use App\GatewayTransaction;
use App\GatewayTransactionHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class CronUpdateTransactionLog
 * This class is tightly coupled to ECS Gateway for now
 *
 * @package App\Console\Commands
 */
class CronUpdateTransactionLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:transaction_log {--start_date?} {{--end_date?}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query the gateway for new transaction statuses and log the changes';

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
        $endDate = new Carbon($this->option('end_date')) ?? Carbon::now('UTC');
        $startDate = new Carbon($this->option('start_date')) ?? $endDate->copy()->subDays(2);
        $dateFormat = 'YmdHis';

        $query = new ECSQuery();
        $results = $query->where('start_date', $startDate->format($dateFormat))
            ->where('end_date', $endDate->format($dateFormat))
            ->get();

        foreach($results as $result) {
            // Get transaction and status
            $transactionId = (string) $result->transaction_id;
            $status = (string) $result->condition;
            $transaction = GatewayTransaction::where('transaction_id', $transactionId)->first();
            if (!$transaction) continue;

            $changes = 0;
            foreach($result->action as $action) {
                $actionType = (string) $action->action_type;
                $date = Carbon::createFromFormat($dateFormat, $action->date);
                $amount = (float) $action->amount;
                // Record history if item is not recorded, increment $changes
                if ( ! $transaction->history()
                    ->where('action', $actionType)
                    ->where('created_at', $date)
                    ->exists()
                ) {
                    $history = new GatewayTransactionHistory([
                        'action' => $actionType,
                        'status' => $status,
                        'amount' => $amount,
                        'created_at' => $date,
                    ]);
                    $transaction->history()->save($history);
                    $changes++;
                }
            }

            if ($changes > 0 && $status == 'failed') {
                event(new FailedTransaction($transaction));
            }
        }
    }
}
