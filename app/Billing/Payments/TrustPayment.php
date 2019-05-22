<?php
namespace App\Billing\Payments;

use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Payments\Methods\Trust;

class TrustPayment implements PaymentMethodStrategy
{
    /**
     * @var \App\Billing\Payments\Methods\Trust
     */
    protected $trust;

    public function __construct(Trust $trust)
    {
        $this->trust = $trust;
    }

    public function charge(float $amount, string $currency = "USD"): ?GatewayTransaction
    {
        $transaction = new GatewayTransaction([
            'gateway_id' => 'trust_receivable',
            'transaction_id' => uniqid('trust'),
            'transaction_type' => 'debit',
            'amount' => $amount,
            'success' => true,
        ]);
        $transaction->method()->associate($this->trust);
        if ($transaction->save()) {
            return $transaction;
        }

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
        return $this->trust;
    }
}