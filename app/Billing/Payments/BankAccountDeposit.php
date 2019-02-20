<?php
namespace App\Billing\Payments;

use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Contracts\DepositMethodStrategy;
use App\Billing\Payments\Methods\BankAccount;

class BankAccountDeposit implements DepositMethodStrategy
{
    /**
     * @var \App\Billing\Payments\Methods\BankAccount
     */
    protected $account;
    /**
     * @var \App\Billing\Gateway\ACHDepositInterface
     */
    protected $gateway;

    public function __construct(BankAccount $account, ACHDepositInterface $gateway = null)
    {
        $this->account = $account;
        $this->gateway = $gateway ?: app(ACHDepositInterface::class);
    }

    public function deposit(float $amount, string $currency = "USD"): ?GatewayTransaction
    {
        return $this->gateway->depositFunds($this->account, $amount, $currency);
    }

}