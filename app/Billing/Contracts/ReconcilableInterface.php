<?php

namespace App\Billing\Contracts;

interface ReconcilableInterface
{
    /**
     * Prepare a query for all gateway transactions that relate to this model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allTransactionsQuery();

    /**
     * Get all gateway transactions that relate to this model
     *
     * @return \App\Billing\GatewayTransaction[]|\Illuminate\Support\Collection
     */
    public function getAllTransactions();
}