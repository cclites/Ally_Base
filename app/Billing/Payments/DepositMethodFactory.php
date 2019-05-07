<?php
namespace App\Billing\Payments;


use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Payments\Methods\BankAccount;

class DepositMethodFactory
{
    /**
     * @var \App\Billing\Gateway\ACHDepositInterface
     */
    protected $achGateway;

    public function __construct(ACHDepositInterface $achGateway = null)
    {
        $this->achGateway = $achGateway ?: app(ACHDepositInterface::class);
    }

    public function getACHGateway(): ACHDepositInterface
    {
        return clone $this->achGateway;
    }

    public function getStrategy(ClaimTransmitterInterface $depositMethod)
    {
        if ($depositMethod instanceof BankAccount)
            return new BankAccountDeposit($depositMethod, $this->getACHGateway());

        return new \InvalidArgumentException("Unsupported Depositable");
    }
}