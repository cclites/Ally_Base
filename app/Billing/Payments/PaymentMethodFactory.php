<?php


namespace App\Billing\Payments;


use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Gateway\ACHPaymentInterface;
use App\Billing\Gateway\CreditCardPaymentInterface;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\Methods\ProviderPayment;
use App\Billing\Payments\Methods\Trust;
use App\Business;

class PaymentMethodFactory
{
    /**
     * @var \App\Billing\Gateway\ACHPaymentInterface
     */
    protected $achGateway;
    /**
     * @var \App\Billing\Gateway\CreditCardPaymentInterface
     */
    protected $ccGateway;

    function __construct(?ACHPaymentInterface $achGateway = null, ?CreditCardPaymentInterface $ccGateway = null)
    {
        $this->achGateway = $achGateway ?: app(ACHPaymentInterface::class);
        $this->ccGateway = $ccGateway ?: app(CreditCardPaymentInterface::class);
    }

    public function getCCGateway(): CreditCardPaymentInterface
    {
        return clone $this->ccGateway;
    }

    public function getACHGateway(): ACHPaymentInterface
    {
        return clone $this->achGateway;
    }

    public function getStrategy(ChargeableInterface $paymentMethod): PaymentMethodStrategy
    {
        if ($paymentMethod instanceof CreditCard)
            return new CreditCardPayment($paymentMethod, $this->getCCGateway());

        if ($paymentMethod instanceof BankAccount)
            return new BankAccountPayment($paymentMethod, $this->getACHGateway());

        if ($paymentMethod instanceof Business)
            return new ProviderPayment($paymentMethod, $this->getACHGateway());

        if ($paymentMethod instanceof Trust)
            return new TrustPayment($paymentMethod);
    }
}