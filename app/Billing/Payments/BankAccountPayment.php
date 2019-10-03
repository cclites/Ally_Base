<?php
namespace App\Billing\Payments;

use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Gateway\ACHPaymentInterface;
use App\Billing\Payments\Methods\BankAccount;

class BankAccountPayment implements PaymentMethodStrategy
{
    /**
     * @var \App\Billing\Payments\Methods\BankAccount
     */
    protected $account;

    /**
     * @var \App\Billing\Gateway\ACHPaymentInterface
     */
    protected $gateway;

    public function __construct(BankAccount $account, ACHPaymentInterface $gateway = null)
    {
        $this->account = $account;
        $this->gateway = $gateway ?: app(ACHPaymentInterface::class);
    }

    public function charge(float $amount, string $currency = 'USD'): ?GatewayTransaction
    {
        if ($address = $this->account->getBillingAddress()) {
            $this->gateway->setBillingAddress($address);
        }

        if ($phone = $this->account->getBillingPhone()) {
            $this->gateway->setBillingPhone($phone);
        }
        
        return $this->gateway->chargeAccount($this->account, $amount, $currency);
    }

    public function refund(?GatewayTransaction $transaction, float $amount, string $currency = "USD"): ?GatewayTransaction
    {
        return $this->gateway->depositFunds($this->account, $amount, $currency);
    }

    public function getPaymentMethod(): ChargeableInterface
    {
        return $this->account;
    }
}