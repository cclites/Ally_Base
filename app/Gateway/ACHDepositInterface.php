<?php

namespace App\Gateway;

use App\Billing\Payments\Methods\BankAccount;

interface ACHDepositInterface
{
    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\Billing\Payments\Methods\BankAccount $account
     *
     * @return \App\Billing\GatewayTransaction
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined|\App\Billing\Exceptions\PaymentMethodError
     */
    public function validateAccount(BankAccount $account);

    /**
     * Deposit (credit) the account with $amount
     *
     * @param \App\Billing\Payments\Methods\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\Billing\GatewayTransaction
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined|\App\Billing\Exceptions\PaymentMethodError
     */
    public function depositFunds(BankAccount $account, $amount, $currency='USD', $secCode='PPD');
}
