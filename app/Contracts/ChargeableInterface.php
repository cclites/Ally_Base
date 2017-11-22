<?php
namespace App\Contracts;

interface ChargeableInterface
{
    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD');
}