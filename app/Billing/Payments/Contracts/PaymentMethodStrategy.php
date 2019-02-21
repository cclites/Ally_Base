<?php
namespace App\Billing\Payments\Contracts;

use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;

interface PaymentMethodStrategy
{
    public function charge(float $amount, string $currency = "USD"): ?GatewayTransaction;
    public function refund(?GatewayTransaction $transaction, float $amount, string $currency = "USD"): ?GatewayTransaction;
    public function getPaymentMethod(): ChargeableInterface;
    public function getPaymentType(): string;
}