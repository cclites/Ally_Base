<?php

namespace App\Console\Commands;

use App\Events\FailedTransactionFound;
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
    protected $signature = 'cron:transaction_log {--start_date=} {--end_date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query the gateway for new transaction statuses and log the changes';

    /**
     * The gateway query object
     *
     * @var \App\Gateway\ECSQuery
     */
    protected $query;

    /**
     * The date format received from the gateway
     *
     * @var string
     */
    protected $dateFormat = 'YmdHis';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ECSQuery $query = null)
    {
        parent::__construct();
        $this->query = ($query) ? $query : new ECSQuery();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $endDate = $this->option('end_date') ? (new Carbon($this->option('end_date')))->setTime(23, 59, 59) : Carbon::now('UTC');
        $startDate = $this->option('start_date') ? (new Carbon($this->option('start_date')))->setTime(0, 0, 0) : $endDate->copy()->subDays(2);

        $results = $this->query->where('start_date', $startDate->format($this->dateFormat))
                               ->where('end_date', $endDate->format($this->dateFormat))
                               ->get();

        foreach($results as $result) {
            // Get transaction and status
            $transactionId = (string) $result->transaction_id;
            $status = (string) $result->condition;
            $transaction = GatewayTransaction::where('transaction_id', $transactionId)->first();
            if (!$transaction) {
                $transaction = $this->createTransaction($transactionId);
            }

            $changes = 0;
            foreach($result->action as $action) {
                $actionType = (string) $action->action_type;
                $date = Carbon::createFromFormat($this->dateFormat, $action->date);
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
                $transaction->foundFailure();
            }
        }
    }

    protected function createTransaction($transactionId)
    {
        $result = $this->query->find($transactionId)->transaction;
        $action = $result->action[0];
        $this->output->writeln("Creating transaction record for ID " . $transactionId);
        return GatewayTransaction::create([
            'gateway_id' => 'ecs',
            'transaction_id' => (string) $result->transaction_id,
            'transaction_type' => (string) $action->action_type,
            'amount' => ($action->amount < 0.0) ? $action->amount * -1 : (float) $action->amount,
            'success' => ($result->condition == 'complete'),
            'created_at' => Carbon::createFromFormat($this->dateFormat, $action->date),
        ]);
    }
}
