<?php

namespace App\Gateway;

use App\Address;
use App\BankAccount;
use App\PhoneNumber;

interface ACHPaymentInterface
{
    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\BankAccount $account
     *
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function validateAccount(BankAccount $account);

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function authorizeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD');

    /**
     * Charge the payment method
     *
     * @param \App\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function chargeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD');

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
