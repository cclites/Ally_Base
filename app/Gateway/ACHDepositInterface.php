<?php

namespace App\Gateway;

use App\BankAccount;

interface ACHDepositInterface
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
     * Deposit (credit) the account with $amount
     *
     * @param \App\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function depositFunds(BankAccount $account, $amount, $currency='USD', $secCode='PPD');
}
