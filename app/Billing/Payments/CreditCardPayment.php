<?php
namespace App\Billing\Payments;

use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Gateway\CreditCardPaymentInterface;
use App\Billing\Payments\Methods\CreditCard;

class CreditCardPayment implements PaymentMethodStrategy
{
    /**
     * @var \App\Billing\Payments\Methods\CreditCard
     */
    protected $card;

    /**
     * @var \App\Gateway\CreditCardPaymentInterface
     */
    protected $gateway;

    public function __construct(CreditCard $card, CreditCardPaymentInterface $gateway = null)
    {
        $this->card = $card;
        $this->gateway = $gateway ?: app(CreditCardPaymentInterface::class);
    }

    public function charge(float $amount, string $currency = 'USD'): ?GatewayTransaction
    {
        if ($address = $this->card->getBillingAddress()) {
            $this->gateway->setBillingAddress($address);
        }

        if ($phone = $this->card->getBillingPhone()) {
            $this->gateway->setBillingPhone($phone);
        }


        return $this->gateway->chargeCard($this->card, $amount, $currency);
    }

    public function refund(?GatewayTransaction $transaction, float $amount, string $currency = "USD"): ?GatewayTransaction
    {
        if ($transaction->method->is($this->card)) {
            return $this->gateway->refund($transaction, $amount);
        }
        throw new PaymentMethodError("The provided credit card does not match the transaction.");
    }
}