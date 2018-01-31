<?php

namespace App\Reports;

use App\Contracts\ReconcilableInterface;
use App\GatewayTransaction;

class AdminReconciliationReport extends BaseReport
{
    /**
     * @var ReconcilableInterface
     */
    protected $reconcilable;

    /**
     * @param \App\Contracts\ReconcilableInterface $reconcilable
     */
    public function __construct(ReconcilableInterface $reconcilable)
    {
        $this->reconcilable = $reconcilable;
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->reconcilable->allTransactionsQuery();
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        return $this->query()
                    ->orderBy('created_at')
                    ->get()
                    ->map(function (GatewayTransaction $transaction) {
                        $transaction->net_amount = 0;
                        if ($transaction->lastHistory->status !== 'failed') {
                            $transaction->net_amount = $transaction->amount;
                            if (in_array($transaction->transaction_type, ['refund', 'credit'])) {
                                $transaction->net_amount *= -1;
                            }
                        }
                        return $transaction;
                    });
    }
}