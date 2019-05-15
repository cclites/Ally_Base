<?php
namespace App\Billing\Payments;

use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Payments\Methods\Offline;

class OfflinePayment implements PaymentMethodStrategy
{
    public function charge(float $amount, string $currency = "USD"): ?GatewayTransaction
    {
        return null;
    }

    public function refund(
        ?GatewayTransaction $transaction,
        float $amount,
        string $currency = "USD"
    ): ?GatewayTransaction {
        return null;
    }

    public function getPaymentMethod(): ChargeableInterface
    {
        return new Offline();
    }
}