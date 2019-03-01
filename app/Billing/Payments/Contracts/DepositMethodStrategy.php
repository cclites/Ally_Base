<?php
namespace App\Billing\Payments\Contracts;

use App\Billing\GatewayTransaction;

interface DepositMethodStrategy
{
    public function deposit(float $amount, string $currency = "USD"): ?GatewayTransaction;
}