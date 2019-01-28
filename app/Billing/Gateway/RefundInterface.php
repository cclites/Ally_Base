<?php
namespace App\Billing\Gateway;

use App\Billing\GatewayTransaction;

interface RefundInterface
{
    /**
     * @param \App\Billing\GatewayTransaction $transaction
     * @param float $amount
     * @return \App\Billing\GatewayTransaction|false
     */
    public function refund(GatewayTransaction $transaction, $amount);
}