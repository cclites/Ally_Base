<?php
namespace App\Billing\Payments\Methods;

use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Gateway\ACHPaymentInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\BankAccountPayment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Business;

class ProviderPayment implements PaymentMethodStrategy
{
    /**
     * @var \App\Business
     */
    protected $business;
    /**
     * @var \App\Billing\Gateway\ACHPaymentInterface
     */
    protected $gateway;
    /**
     * @var \App\Billing\Payments\BankAccountPayment
     */
    private $bankStrategy;


    public function __construct(Business $business, ACHPaymentInterface $gateway)
    {
        $this->business = $business;
        $this->gateway = $gateway ?: app(ACHPaymentInterface::class);
        if (!$this->business->paymentAccount) {
            throw new PaymentMethodError("There is no payment account assigned to this business.");
        }
        $this->bankStrategy = new BankAccountPayment($this->business->paymentAccount, $this->gateway);
    }

    public function charge(float $amount, string $currency = "USD"): ?GatewayTransaction
    {
        return $this->bankStrategy->charge($amount, $currency);
    }

    public function refund(
        ?GatewayTransaction $transaction,
        float $amount,
        string $currency = "USD"
    ): ?GatewayTransaction
    {
        return $this->bankStrategy->refund($transaction, $amount, $currency);
    }

    public function getPaymentMethod(): ChargeableInterface
    {
        return $this->business;
    }
}