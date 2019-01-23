<?php

namespace App\Gateway;

use App\Billing\PaymentMethods\BankAccount;

interface ACHDepositInterface
{
    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\Billing\PaymentMethods\BankAccount $account
     *
     * @return \App\Billing\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function validateAccount(BankAccount $account);

    /**
     * Deposit (credit) the account with $amount
     *
     * @param \App\Billing\PaymentMethods\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\Billing\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function depositFunds(BankAccount $account, $amount, $currency='USD', $secCode='PPD');
}
