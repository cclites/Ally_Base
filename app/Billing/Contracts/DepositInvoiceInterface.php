<?php


namespace App\Billing\Contracts;


use App\Billing\Actions\ProcessDeposit;
use App\Billing\Deposit;
use App\Billing\Payments\Contracts\DepositMethodStrategy;
use Illuminate\Support\Collection;

interface DepositInvoiceInterface
{
    public function addDeposit(Deposit $deposit, float $amountApplied): bool;
    public function getAmount(): float;
    public function getAmountDue(): float;
    public function getItems(): Collection;
}