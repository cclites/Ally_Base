<?php

namespace App\Payments;

class BusinessPaymentAggregatorWithoutClients extends BusinessPaymentAggregator
{
    /**
     * Get all client models using the provider payment method
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Client[]
     */
    public function getClientsUsingProviderPayment()
    {
        return collect([]);
    }

    /**
     * Get all client IDs using the provider payment method
     *
     * @return array
     */
    public function getClientIdsUsingProviderPayment()
    {
        return [];
    }
}
