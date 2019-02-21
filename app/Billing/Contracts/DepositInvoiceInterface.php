<?php


namespace App\Billing\Contracts;


use App\Billing\Actions\ProcessDeposit;
use App\Billing\Deposit;
use App\Billing\Payments\Contracts\DepositMethodStrategy;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

interface DepositInvoiceInterface extends InvoiceInterface
{
    public function addDeposit(Deposit $deposit, float $amountApplied): bool;
    public function getItems(): Collection;
    public function getRecipient(): ContactableInterface;
}