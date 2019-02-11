<?php


namespace App\Traits;

use App\Billing\GatewayTransaction;

trait ChargedTransactionsTrait
{
    private $chargeMetrics;

    /**
     * Relationship for all charged transactions for this payment method
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function chargedTransactions()
    {
        return $this->morphMany(GatewayTransaction::class, 'method');
    }

    /**
     * Get metrics on successful charges for this payment method.
     *
     * @return object
     */
    public function getChargeMetrics()
    {
        if ($this->chargeMetrics) return $this->chargeMetrics;

        return $this->chargeMetrics = $this->chargedTransactions()
            ->where('success', 1)
            ->select(\DB::raw('COUNT(*) as successful_charge_count, MIN(gateway_transactions.created_at) as first_charge_date, MAX(gateway_transactions.created_at) as last_charge_date'))
            ->first();
    }

    /**
     * Add attribute for charge metrics
     *
     * @return object
     */
    public function getChargeMetricsAttribute()
    {
        return $this->getChargeMetrics();
    }

}