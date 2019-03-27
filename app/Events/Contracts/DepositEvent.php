<?php


namespace App\Events\Contracts;


use App\Billing\Deposit;

interface DepositEvent
{
    public function __construct(Deposit $deposit);
    public function getDeposit(): Deposit;
}