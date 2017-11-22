<?php

namespace App\Gateway;

use App\Address;
use App\CreditCard;
use App\PhoneNumber;

interface CreditCardPaymentInterface
{
    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\CreditCard $card
     * @param mixed $cvv
     *
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function validateCard(CreditCard $card, $cvv = null);

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param mixed $cvv
     *
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function authorizeCard(CreditCard $card, $amount, $currency = 'USD', $cvv = null);

    /**
     * Charge the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param mixed $cvv
     *
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function chargeCard(CreditCard $card, $amount, $currency = 'USD', $cvv = null);

    /**
     * @param \App\Address $address
     * @return $this
     */
    public function setBillingAddress(Address $address);

    /**
     * @param \App\PhoneNumber $phone
     * @return $this
     */
    public function setBillingPhone(PhoneNumber $phone);
}
