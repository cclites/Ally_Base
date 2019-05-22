<?php
namespace App\Billing\Events;

use App\Billing\Deposit;

interface DepositEvent
{
    public function getDeposit(): Deposit;
}