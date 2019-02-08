<?php

namespace App\Billing\Gateway;

use App\Address;
use App\Billing\Payments\Methods\BankAccount;
use App\PhoneNumber;

interface ACHPaymentInterface extends ACHDepositInterface
{

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param \App\Billing\Payments\Methods\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\Billing\GatewayTransaction
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined|\App\Billing\Exceptions\PaymentMethodError
     */
    public function authorizeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD');

    /**
     * Charge the payment method
     *
     * @param \App\Billing\Payments\Methods\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\Billing\GatewayTransaction
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined|\App\Billing\Exceptions\PaymentMethodError
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
