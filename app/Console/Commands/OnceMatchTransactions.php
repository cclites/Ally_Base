<?php

namespace App\Console\Commands;

use App\Billing\Gateway\ECSQuery;
use App\Billing\GatewayTransaction;
use App\Payments\TransactionMatcher;
use Illuminate\Console\Command;

class OnceMatchTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:match_transactions {startId=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ONE TIME COMMAND FOR MATCHING TRANSACTIONS TO PAYMENT METHODS';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $lastId = $this->argument('startId') - 1;

        do {
            // Grab 100 transactions at a time
            $transactions = GatewayTransaction::where('id', '>', $lastId)
                                              ->orderBy('id')
                                              ->limit(100)
                                              ->get();
            foreach($transactions as $transaction) {
                $lastId = $transaction->id;
                if ($transaction->method) {
                    $this->output->writeln(sprintf('Transaction %d: Method already exists', $transaction->id));
                    continue;
                }

                $matcher = new TransactionMatcher(new ECSQuery());
                if ($method = $matcher->findMethod($transaction->transaction_id)) {
                    $this->output->writeln(sprintf('Transaction %d: Found method %s, %d', $transaction->id, get_class($method), $method->id));
                    $transaction->method()->associate($method);
                    $transaction->save();
                }
                else {
                    $this->output->writeln(sprintf('Transaction %d: NO METHOD FOUND', $transaction->id));
                }

                // SLEEP 1 second in between each findMethod
                sleep(1);
            }
        } while( count($transactions) > 0 );

    }

}
