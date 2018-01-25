<?php
namespace App\Gateway;

use App\GatewayTransaction;

interface RefundInterface
{
    /**
     * @param \App\GatewayTransaction $transaction
     * @param float $amount
     * @return \App\GatewayTransaction|false
     */
    public function refund(GatewayTransaction $transaction, $amount);
}